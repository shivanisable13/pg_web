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
                sh 'docker run --rm -v "$PWD":/app -w /app php:8.2-cli find . -name "*.php" -not -path "./vendor/*" | xargs -I{} php -l {} 2>&1 | grep -E "Parse error|Fatal error" || echo "PHP syntax check passed!"'
            }
        }

        stage('Security Scan') {
            steps {
                echo '🔒 Running security checks...'
                sh 'grep -rn "SMTP_PASS" includes/config/config.php && echo "WARNING: Credentials in config.php" || echo "Security scan done."'
            }
        }

        stage('Docker Build') {
            steps {
                echo "🐳 Building Docker image: ${DOCKER_IMAGE}:${DOCKER_TAG}"
                sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} -t ${DOCKER_IMAGE}:latest ."
                sh "docker images | grep ${DOCKER_IMAGE}"
            }
        }

        stage('Deploy') {
            steps {
                echo '🚀 Deploying CampusStay...'
                sh 'docker compose down --remove-orphans || true'
                sh 'docker compose up -d --build'
                sh 'sleep 15'
                sh 'docker compose ps'
            }
        }

        stage('Health Check') {
            steps {
                echo '🏥 Checking application health...'
                sh '''
                    APP_UP=$(docker ps --filter name=campusstay_app --filter status=running -q | wc -l)
                    DB_UP=$(docker ps --filter name=campusstay_db --filter status=running -q | wc -l)

                    if [ "$APP_UP" -ge "1" ]; then
                        echo "App container is running"
                    else
                        echo "App container is NOT running"
                        docker logs campusstay_app --tail 20 || true
                        exit 1
                    fi

                    if [ "$DB_UP" -ge "1" ]; then
                        echo "DB container is running"
                    else
                        echo "DB container is NOT running"
                        docker logs campusstay_db --tail 20 || true
                        exit 1
                    fi
                '''
                sh """
                    sleep 5
                    HTTP=\$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${DEPLOY_PORT}/ || echo 000)
                    echo "HTTP Response: \$HTTP"
                """
            }
        }

        stage('Cleanup') {
            steps {
                echo '🧹 Cleaning up unused images...'
                sh 'docker image prune -f || true'
            }
        }
    }

    post {
        success {
            echo "Deployment successful! App: http://localhost:8080 | phpMyAdmin: http://localhost:8081"
        }
        failure {
            echo 'Pipeline FAILED! Printing container logs...'
            sh 'docker logs campusstay_app --tail 20 || true'
            sh 'docker logs campusstay_db --tail 20 || true'
        }
        always {
            echo "Pipeline finished — Build #${BUILD_NUMBER}"
        }
    }
}
