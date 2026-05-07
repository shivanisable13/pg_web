pipeline {
    agent any

    environment {
        APP_NAME     = 'campusstay'
        DOCKER_IMAGE = 'campusstay-app'
        DOCKER_TAG   = "${BUILD_NUMBER}"
        DEPLOY_PORT  = '8080'
    }

    options {
        timestamps()
        buildDiscarder(logRotator(numToKeepStr: '5'))
        timeout(time: 20, unit: 'MINUTES')
    }

    stages {

        stage('Checkout') {
            steps {
                echo '📦 Cloning source code...'
                checkout scm
                sh 'ls -la'
            }
        }

        stage('PHP Syntax Check') {
            steps {
                echo '🔍 Checking PHP syntax using Docker PHP image...'
                sh '''
                    if docker run --rm -v "$PWD":/app php:8.2-cli bash -c "
                        ERRORS=0
                        for FILE in \$(find /app -name '*.php' -not -path '/app/vendor/*'); do
                            php -l \"\$FILE\" > /dev/null 2>&1 || ERRORS=\$((ERRORS+1))
                        done
                        if [ \"\$ERRORS\" -gt 0 ]; then
                            echo \"Found \$ERRORS PHP syntax error(s)\"
                            exit 1
                        else
                            echo \"All PHP files passed syntax check!\"
                        fi
                    "; then
                        echo "✅ PHP syntax check passed!"
                    else
                        echo "❌ PHP syntax errors found!"
                        exit 1
                    fi
                '''
            }
        }

        stage('Security Scan') {
            steps {
                echo '🔒 Running basic security checks...'
                sh '''
                    echo "Checking for hardcoded credentials..."
                    if grep -rn "SMTP_PASS" includes/config/config.php; then
                        echo "WARNING: Credentials found in config.php - use environment variables in production."
                    fi
                    echo "✅ Security scan complete."
                '''
            }
        }

        stage('Docker Build') {
            steps {
                echo "🐳 Building Docker image: ${DOCKER_IMAGE}:${DOCKER_TAG}"
                sh """
                    docker build \\
                        -t ${DOCKER_IMAGE}:${DOCKER_TAG} \\
                        -t ${DOCKER_IMAGE}:latest \\
                        .
                    echo "✅ Docker image built!"
                    docker images | grep ${DOCKER_IMAGE}
                """
            }
        }

        stage('Deploy') {
            steps {
                echo '🚀 Starting services via Docker Compose...'
                sh '''
                    # Support both docker-compose v1 and docker compose v2
                    if command -v docker-compose > /dev/null 2>&1; then
                        COMPOSE_CMD="docker-compose"
                    else
                        COMPOSE_CMD="docker compose"
                    fi

                    $COMPOSE_CMD down --remove-orphans || true
                    $COMPOSE_CMD up -d --build
                    echo "⏳ Waiting for services..."
                    sleep 15
                    $COMPOSE_CMD ps
                '''
            }
        }

        stage('Health Check') {
            steps {
                echo '🏥 Verifying application health...'
                sh """
                    if command -v docker-compose > /dev/null 2>&1; then
                        COMPOSE_CMD="docker-compose"
                    else
                        COMPOSE_CMD="docker compose"
                    fi

                    APP_UP=\$(\$COMPOSE_CMD ps | grep campusstay_app | grep -c Up || echo 0)
                    DB_UP=\$(\$COMPOSE_CMD ps | grep campusstay_db | grep -c Up || echo 0)

                    if [ "\$APP_UP" -ge "1" ]; then
                        echo "✅ App container is UP"
                    else
                        echo "❌ App container is DOWN"
                        \$COMPOSE_CMD logs app --tail=30
                        exit 1
                    fi

                    if [ "\$DB_UP" -ge "1" ]; then
                        echo "✅ DB container is UP"
                    else
                        echo "❌ DB container is DOWN"
                        \$COMPOSE_CMD logs db --tail=30
                        exit 1
                    fi

                    sleep 5
                    HTTP_CODE=\$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${DEPLOY_PORT}/ || echo 000)
                    echo "HTTP Response: \$HTTP_CODE"
                    if [ "\$HTTP_CODE" = "200" ] || [ "\$HTTP_CODE" = "302" ]; then
                        echo "✅ Application is live on port ${DEPLOY_PORT}"
                    else
                        echo "⚠️  App returned HTTP \$HTTP_CODE"
                    fi
                """
            }
        }

        stage('Cleanup') {
            steps {
                echo '🧹 Removing unused Docker images...'
                sh 'docker image prune -f || true'
            }
        }
    }

    post {
        success {
            echo "✅ CampusStay deployed! App: http://localhost:8080 | phpMyAdmin: http://localhost:8081"
        }
        failure {
            echo '❌ Pipeline FAILED! Check logs above.'
            sh '''
                if command -v docker-compose > /dev/null 2>&1; then
                    docker-compose logs --tail=30 || true
                else
                    docker compose logs --tail=30 || true
                fi
            '''
        }
        always {
            echo "Pipeline finished — Build #${BUILD_NUMBER}"
        }
    }
}
