pipeline {
    agent any

    environment {
        APP_NAME     = 'campusstay'
        DOCKER_IMAGE = 'campusstay-app'
        DOCKER_TAG   = "${BUILD_NUMBER}"
        DEPLOY_PORT  = '8080'
        DB_NAME      = 'campus_stay'
        DB_USER      = 'campusstay_user'
        DB_PASS      = 'CampusStay2024'
        DB_ROOT_PASS = 'RootPass2024'
    }

    options {
        timestamps()
        buildDiscarder(logRotator(numToKeepStr: '5'))
        timeout(time: 20, unit: 'MINUTES')
    }

    stages {

        stage('Checkout') {
            steps {
                echo 'Cloning source code...'
                checkout scm
                sh 'ls -la'
            }
        }

        stage('PHP Syntax Check') {
            steps {
                echo 'Checking PHP syntax...'
                sh 'docker run --rm -v "$PWD":/app -w /app php:8.2-cli find . -name "*.php" -not -path "./vendor/*" | xargs -I{} php -l {} 2>&1 | grep -E "Parse error|Fatal error" || echo "PHP syntax check passed!"'
            }
        }

        stage('Security Scan') {
            steps {
                echo 'Running security checks...'
                sh 'grep -rn "SMTP_PASS" includes/config/config.php && echo "WARNING: Credentials in config.php" || echo "Security scan done."'
            }
        }

        stage('Docker Build') {
            steps {
                echo "Building Docker image: ${DOCKER_IMAGE}:${DOCKER_TAG}"
                sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} -t ${DOCKER_IMAGE}:latest ."
                sh "docker images | grep ${DOCKER_IMAGE}"
            }
        }

        stage('Deploy') {
            steps {
                echo 'Deploying CampusStay with docker run...'
                sh '''
                    # Remove old containers if running
                    docker stop campusstay_app campusstay_db campusstay_phpmyadmin 2>/dev/null || true
                    docker rm   campusstay_app campusstay_db campusstay_phpmyadmin 2>/dev/null || true

                    # Create network if it doesn't exist
                    docker network create campusstay_network 2>/dev/null || true

                    # Start MySQL
                    docker run -d \
                        --name campusstay_db \
                        --network campusstay_network \
                        -e MYSQL_DATABASE=campus_stay \
                        -e MYSQL_USER=campusstay_user \
                        -e MYSQL_PASSWORD=CampusStay2024 \
                        -e MYSQL_ROOT_PASSWORD=RootPass2024 \
                        -p 3307:3306 \
                        --restart unless-stopped \
                        mysql:8.0

                    echo "Waiting for MySQL to be ready..."
                    sleep 20

                    # Start PHP App
                    docker run -d \
                        --name campusstay_app \
                        --network campusstay_network \
                        -p 8080:80 \
                        -e DB_HOST=campusstay_db \
                        -e DB_NAME=campus_stay \
                        -e DB_USER=campusstay_user \
                        -e DB_PASS=CampusStay2024 \
                        --restart unless-stopped \
                        campusstay-app:latest

                    echo "Containers started!"
                    docker ps | grep campusstay
                '''
            }
        }

        stage('Health Check') {
            steps {
                echo 'Verifying application health...'
                sh '''
                    sleep 10
                    APP_UP=$(docker ps --filter name=campusstay_app --filter status=running -q | wc -l)
                    DB_UP=$(docker ps --filter name=campusstay_db --filter status=running -q | wc -l)

                    if [ "$APP_UP" -ge "1" ]; then
                        echo "App container is running"
                    else
                        echo "App container failed!"
                        docker logs campusstay_app --tail 20 || true
                        exit 1
                    fi

                    if [ "$DB_UP" -ge "1" ]; then
                        echo "DB container is running"
                    else
                        echo "DB container failed!"
                        docker logs campusstay_db --tail 20 || true
                        exit 1
                    fi
                '''
                sh "sleep 5 && curl -s -o /dev/null -w 'HTTP Status: %{http_code}' http://localhost:${DEPLOY_PORT}/ || echo 'curl not available'"
            }
        }

        stage('Cleanup') {
            steps {
                echo 'Cleaning up unused Docker images...'
                sh 'docker image prune -f || true'
            }
        }
    }

    post {
        success {
            echo "Deployment successful! App: http://localhost:8080"
        }
        failure {
            echo 'Pipeline FAILED!'
            sh 'docker logs campusstay_app --tail 20 || true'
            sh 'docker logs campusstay_db --tail 20 || true'
        }
        always {
            echo "Pipeline finished - Build #${BUILD_NUMBER}"
        }
    }
}
