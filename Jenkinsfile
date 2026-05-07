pipeline {

    agent any

    environment {
        DOCKER_IMAGE = 'campusstay-app'
        DOCKER_TAG   = "${BUILD_NUMBER}"
        DEPLOY_PORT  = '9090'
    }

    options {
        timestamps()
        buildDiscarder(logRotator(numToKeepStr: '5'))
        timeout(time: 30, unit: 'MINUTES')
    }

    stages {

        // ==========================================
        // CHECKOUT SOURCE CODE
        // ==========================================
        stage('Checkout') {
            steps {
                echo 'Cloning GitHub repository...'

                checkout scm

                sh 'ls -la'
            }
        }

        // ==========================================
        // PHP SYNTAX CHECK
        // ==========================================
        stage('PHP Syntax Check') {
            steps {
                echo 'Checking PHP syntax...'

                sh '''
                    docker run --rm \
                    -v "$PWD":/app \
                    -w /app \
                    php:8.2-cli \
                    sh -c "find . -name '*.php' ! -path './vendor/*' -exec php -l {} \\;"
                '''
            }
        }

        // ==========================================
        // SECURITY CHECK
        // ==========================================
        stage('Security Scan') {
            steps {
                echo 'Running security scan...'

                sh '''
                    grep -rn "SMTP_PASS" includes/config/config.php \
                    && echo "WARNING: Credentials detected in config.php" \
                    || echo "Security scan completed."
                '''
            }
        }

        // ==========================================
        // BUILD DOCKER IMAGE
        // ==========================================
        stage('Docker Build') {
            steps {

                echo "Building Docker image..."

                sh """
                    docker build \
                    -t ${DOCKER_IMAGE}:${DOCKER_TAG} \
                    -t ${DOCKER_IMAGE}:latest .
                """

                sh 'docker images | grep campusstay-app || true'
            }
        }

        // ==========================================
        // DEPLOY APPLICATION
        // ==========================================
        stage('Deploy') {
            steps {

                echo 'Deploying CampusStay application...'

                sh '''
                    echo "======================================"
                    echo "STOPPING OLD CONTAINERS"
                    echo "======================================"

                    docker stop campusstay_app || true
                    docker stop campusstay_db || true

                    docker rm -f campusstay_app || true
                    docker rm -f campusstay_db || true

                    echo "======================================"
                    echo "REMOVING OLD NETWORK"
                    echo "======================================"

                    docker network rm campusstay_network || true

                    sleep 2

                    docker network create campusstay_network

                    echo "======================================"
                    echo "CREATING MYSQL VOLUME"
                    echo "======================================"

                    docker volume create campusstay_mysql_data || true

                    echo "======================================"
                    echo "STARTING MYSQL CONTAINER"
                    echo "======================================"

                    docker run -d \
                        --name campusstay_db \
                        --network campusstay_network \
                        -e MYSQL_DATABASE=campusstay \
                        -e MYSQL_USER=campusstay_user \
                        -e MYSQL_PASSWORD=CampusStay2024 \
                        -e MYSQL_ROOT_PASSWORD=RootPass2024 \
                        -p 3307:3306 \
                        -v campusstay_mysql_data:/var/lib/mysql \
                        mysql:8.0

                    echo "Waiting for MySQL to initialize..."
                    sleep 35

                    echo "======================================"
                    echo "STARTING PHP APPLICATION"
                    echo "======================================"

                    docker run -d \
                        --name campusstay_app \
                        --network campusstay_network \
                        -p 9090:80 \
                        -e DB_HOST=campusstay_db \
                        -e DB_NAME=campusstay \
                        -e DB_USER=campusstay_user \
                        -e DB_PASS=CampusStay2024 \
                        campusstay-app:latest

                    echo "======================================"
                    echo "RUNNING CONTAINERS"
                    echo "======================================"

                    docker ps
                '''
            }
        }

        // ==========================================
        // HEALTH CHECK
        // ==========================================
        stage('Health Check') {
            steps {

                echo 'Performing health check...'

                sh '''
                    sleep 10

                    APP_STATUS=$(docker ps \
                        --filter name=campusstay_app \
                        --filter status=running \
                        -q | wc -l)

                    DB_STATUS=$(docker ps \
                        --filter name=campusstay_db \
                        --filter status=running \
                        -q | wc -l)

                    if [ "$APP_STATUS" -ge "1" ]; then
                        echo "SUCCESS: App container is running"
                    else
                        echo "ERROR: App container failed"

                        docker logs campusstay_app || true

                        exit 1
                    fi

                    if [ "$DB_STATUS" -ge "1" ]; then
                        echo "SUCCESS: DB container is running"
                    else
                        echo "ERROR: DB container failed"

                        docker logs campusstay_db || true

                        exit 1
                    fi

                    HTTP_CODE=$(curl -s -o /dev/null \
                        -w "%{http_code}" \
                        http://localhost:9090/ || echo 000)

                    echo "HTTP STATUS: $HTTP_CODE"

                    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
                        echo "SUCCESS: Website is accessible!"
                    else
                        echo "WARNING: Website returned HTTP $HTTP_CODE"
                    fi
                '''
            }
        }

        // ==========================================
        // CLEANUP
        // ==========================================
        stage('Cleanup') {
            steps {

                echo 'Cleaning unused Docker images...'

                sh '''
                    docker image prune -f || true
                '''
            }
        }
    }

    // ==========================================
    // POST BUILD ACTIONS
    // ==========================================
    post {

        success {

            echo '======================================'
            echo 'DEPLOYMENT SUCCESSFUL!'
            echo '======================================'

            echo "CampusStay URL:"
            echo "http://localhost:9090"
        }

        failure {

            echo '======================================'
            echo 'DEPLOYMENT FAILED!'
            echo '======================================'

            sh 'docker logs campusstay_app || true'

            sh 'docker logs campusstay_db || true'
        }

        always {

            echo '======================================'
            echo 'RUNNING CONTAINERS'
            echo '======================================'

            sh 'docker ps || true'

            echo "Build #${BUILD_NUMBER} completed."
        }
    }
}
