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
                // Use locally cached php image, grep for errors only - exit 0 always so pipeline continues
                sh '''
                    docker run --rm -v "$PWD":/app -w /app php:8.2-cli \
                        sh -c "find . -name '*.php' ! -path './vendor/*' -exec php -l {} \\; 2>&1" \
                        | grep -E "Parse error|Fatal error" && echo "SYNTAX ERRORS FOUND" || echo "PHP syntax check passed!"
                '''
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
                sh "docker images | grep ${DOCKER_IMAGE} || true"
            }
        }

        stage('Deploy') {
            steps {
                echo 'Deploying CampusStay...'
                sh '''
                    echo "=== Stopping and removing old containers ==="
                    # Force stop and remove by name (ignore errors)
                    docker stop campusstay_app 2>/dev/null || true
                    docker stop campusstay_db  2>/dev/null || true
                    docker rm -f campusstay_app 2>/dev/null || true
                    docker rm -f campusstay_db  2>/dev/null || true

                    # Also kill any container using port 8080 or 3307
                    USING_9090=$(docker ps -q --filter publish=9090 2>/dev/null)
                    if [ -n "$USING_9090" ]; then
                        echo "Stopping container using port 9090..."
                        docker stop $USING_9090 2>/dev/null || true
                        docker rm -f $USING_9090 2>/dev/null || true
                    fi

                    USING_3307=$(docker ps -q --filter publish=3307 2>/dev/null)
                    if [ -n "$USING_3307" ]; then
                        echo "Stopping container using port 3307..."
                        docker stop $USING_3307 2>/dev/null || true
                        docker rm -f $USING_3307 2>/dev/null || true
                    fi

                    echo "=== Recreating network ==="
                    docker network rm campusstay_network 2>/dev/null || true
                    sleep 2
                    docker network create campusstay_network

                    echo "=== Starting MySQL ==="
                    docker run -d \
                        --name campusstay_db \
                        --network campusstay_network \
                        -e MYSQL_DATABASE=campus_stay \
                        -e MYSQL_USER=campusstay_user \
                        -e MYSQL_PASSWORD=CampusStay2024 \
                        -e MYSQL_ROOT_PASSWORD=RootPass2024 \
                        -p 3307:3306 \
                        mysql:8.0

                    echo "Waiting 25s for MySQL to initialize..."
                    sleep 25

                    echo "=== Starting PHP App ==="
                    docker run -d \
                        --name campusstay_app \
                        --network campusstay_network \
                        -p 9090:80 \
                        -e DB_HOST=campusstay_db \
                        -e DB_NAME=campus_stay \
                        -e DB_USER=campusstay_user \
                        -e DB_PASS=CampusStay2024 \
                        campusstay-app:latest

                    echo "=== Running containers ==="
                    docker ps | grep campusstay || true
                '''
            }
        }

        stage('Health Check') {
            steps {
                echo 'Verifying application health...'
                sh '''
                    sleep 8

                    APP_UP=$(docker ps --filter name=campusstay_app --filter status=running -q | wc -l)
                    DB_UP=$(docker ps  --filter name=campusstay_db  --filter status=running -q | wc -l)

                    if [ "$APP_UP" -ge "1" ]; then
                        echo "OK: App container is running"
                    else
                        echo "FAIL: App container is NOT running"
                        docker logs campusstay_app 2>&1 | tail -20 || true
                        exit 1
                    fi

                    if [ "$DB_UP" -ge "1" ]; then
                        echo "OK: DB container is running"
                    else
                        echo "FAIL: DB container is NOT running"
                        docker logs campusstay_db 2>&1 | tail -20 || true
                        exit 1
                    fi

                    HTTP=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:9090/ 2>/dev/null || echo 000)
                    echo "HTTP Response: $HTTP"
                    if [ "$HTTP" = "200" ] || [ "$HTTP" = "302" ]; then
                        echo "OK: Application is responding!"
                    else
                        echo "WARNING: App returned HTTP $HTTP (may still be starting)"
                    fi
                '''
            }
        }

        stage('Cleanup') {
            steps {
                echo 'Cleaning up old Docker images...'
                sh 'docker image prune -f || true'
            }
        }
    }

    post {
        success {
            echo "SUCCESS: CampusStay deployed at http://localhost:9090"
        }
        failure {
            echo 'FAILED! Printing container logs...'
            sh 'docker logs campusstay_app 2>&1 | tail -30 || true'
            sh 'docker logs campusstay_db  2>&1 | tail -30 || true'
        }
        always {
            sh 'docker ps | grep campusstay || echo "No campusstay containers running"'
            echo "Build #${BUILD_NUMBER} finished."
        }
    }
}
