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
                echo '🔍 Checking PHP syntax...'
                sh '''
                    ERRORS=0
                    for FILE in $(find . -name "*.php" -not -path "./vendor/*"); do
                        php -l "$FILE" > /dev/null 2>&1 || ERRORS=$((ERRORS+1))
                    done
                    if [ "$ERRORS" -gt "0" ]; then
                        echo "❌ Found $ERRORS PHP syntax error(s)!"
                        exit 1
                    else
                        echo "✅ All PHP files passed syntax check!"
                    fi
                '''
            }
        }

        stage('Security Scan') {
            steps {
                echo '🔒 Running basic security checks...'
                sh '''
                    echo "Checking for hardcoded passwords..."
                    if grep -rn "SMTP_PASS" includes/config/config.php | grep -v "define"; then
                        echo "WARNING: Review credentials in config.php before production deployment."
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
                        --no-cache \\
                        .
                    echo "✅ Docker image built successfully!"
                    docker images | grep ${DOCKER_IMAGE}
                """
            }
        }

        stage('Deploy') {
            steps {
                echo '🚀 Starting CampusStay services via Docker Compose...'
                sh '''
                    docker-compose down --remove-orphans || true
                    docker-compose up -d --build
                    echo "⏳ Waiting for services to start..."
                    sleep 15
                    docker-compose ps
                '''
            }
        }

        stage('Health Check') {
            steps {
                echo '🏥 Verifying application health...'
                sh """
                    APP_RUNNING=\$(docker-compose ps | grep campusstay_app | grep Up | wc -l)
                    DB_RUNNING=\$(docker-compose ps | grep campusstay_db | grep Up | wc -l)

                    if [ "\$APP_RUNNING" = "1" ]; then
                        echo "✅ App container is running"
                    else
                        echo "❌ App container is NOT running"
                        docker-compose logs app --tail=30
                        exit 1
                    fi

                    if [ "\$DB_RUNNING" = "1" ]; then
                        echo "✅ Database container is running"
                    else
                        echo "❌ Database container is NOT running"
                        docker-compose logs db --tail=30
                        exit 1
                    fi

                    sleep 5
                    HTTP_CODE=\$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${DEPLOY_PORT}/ || echo 000)
                    echo "HTTP Response: \$HTTP_CODE"
                    if [ "\$HTTP_CODE" = "200" ] || [ "\$HTTP_CODE" = "302" ]; then
                        echo "✅ Application is responding on port ${DEPLOY_PORT}"
                    else
                        echo "⚠️  App returned HTTP \$HTTP_CODE — check container logs"
                        docker-compose logs app --tail=20
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
            sh 'docker-compose logs --tail=30 || true'
        }
        always {
            echo "Pipeline finished — Build #${BUILD_NUMBER}"
        }
    }
}
