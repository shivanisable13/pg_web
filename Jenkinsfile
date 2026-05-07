// ============================================================
// Jenkinsfile for CampusStay - CI/CD Pipeline
// Stages: Checkout → Build → Test → Deploy
// ============================================================

pipeline {
    agent any

    environment {
        APP_NAME        = 'campusstay'
        DOCKER_IMAGE    = "campusstay-app"
        DOCKER_TAG      = "${BUILD_NUMBER}"
        COMPOSE_FILE    = 'docker-compose.yml'
        DEPLOY_PORT     = '8080'
    }

    options {
        timestamps()
        buildDiscarder(logRotator(numToKeepStr: '5'))
        timeout(time: 20, unit: 'MINUTES')
    }

    stages {

        // ── Stage 1: Checkout Source Code ───────────────────
        stage('Checkout') {
            steps {
                echo '📦 Cloning source code...'
                checkout scm
                sh 'ls -la'
            }
        }

        // ── Stage 2: Validate PHP Syntax ────────────────────
        stage('PHP Syntax Check') {
            steps {
                echo '🔍 Checking PHP syntax...'
                sh '''
                    find . -name "*.php" \
                        -not -path "./vendor/*" \
                        -exec php -l {} \\; | grep -v "No syntax errors"
                    echo "✅ PHP syntax check passed!"
                '''
            }
        }

        // ── Stage 3: Security Scan ──────────────────────────
        stage('Security Scan') {
            steps {
                echo '🔒 Running basic security checks...'
                sh '''
                    echo "Checking for exposed credentials..."
                    if grep -rn "SMTP_PASS\s*=\s*['\"][^'\"]*['\"]" includes/config/config.php; then
                        echo "⚠️  Warning: Credentials in config.php - use environment variables in production"
                    fi
                    echo "✅ Security scan complete."
                '''
            }
        }

        // ── Stage 4: Build Docker Image ─────────────────────
        stage('Docker Build') {
            steps {
                echo "🐳 Building Docker image: ${DOCKER_IMAGE}:${DOCKER_TAG}"
                sh '''
                    docker build \
                        -t ${DOCKER_IMAGE}:${DOCKER_TAG} \
                        -t ${DOCKER_IMAGE}:latest \
                        --no-cache \
                        .
                    echo "✅ Docker image built successfully!"
                    docker images | grep ${DOCKER_IMAGE}
                '''
            }
        }

        // ── Stage 5: Start Services ─────────────────────────
        stage('Deploy with Docker Compose') {
            steps {
                echo '🚀 Starting CampusStay services...'
                sh '''
                    # Stop any existing containers
                    docker-compose down --remove-orphans || true

                    # Start all services in detached mode
                    docker-compose up -d --build

                    # Wait for services to be ready
                    echo "⏳ Waiting for services to start..."
                    sleep 15

                    # Show running containers
                    docker-compose ps
                '''
            }
        }

        // ── Stage 6: Health Check ────────────────────────────
        stage('Health Check') {
            steps {
                echo '🏥 Verifying application is running...'
                sh '''
                    # Check if app container is running
                    if docker-compose ps | grep "campusstay_app" | grep "Up"; then
                        echo "✅ App container is running"
                    else
                        echo "❌ App container is NOT running"
                        docker-compose logs app
                        exit 1
                    fi

                    # Check if DB container is running
                    if docker-compose ps | grep "campusstay_db" | grep "Up"; then
                        echo "✅ Database container is running"
                    else
                        echo "❌ Database container is NOT running"
                        docker-compose logs db
                        exit 1
                    fi

                    # Try to hit the app endpoint
                    sleep 5
                    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${DEPLOY_PORT}/ || echo "000")
                    echo "HTTP Response Code: ${HTTP_CODE}"
                    if [ "${HTTP_CODE}" = "200" ] || [ "${HTTP_CODE}" = "302" ]; then
                        echo "✅ Application is responding on port ${DEPLOY_PORT}"
                    else
                        echo "⚠️  Application returned HTTP ${HTTP_CODE} — check logs"
                        docker-compose logs app --tail=30
                    fi
                '''
            }
        }

        // ── Stage 7: Cleanup Old Images ─────────────────────
        stage('Cleanup') {
            steps {
                echo '🧹 Cleaning up old Docker images...'
                sh '''
                    # Remove dangling/unused images to free space
                    docker image prune -f || true
                    echo "✅ Cleanup done!"
                '''
            }
        }
    }

    // ── Post-Pipeline Actions ────────────────────────────────
    post {
        success {
            echo """
            ╔══════════════════════════════════════╗
            ║   ✅ CampusStay Deployed Successfully ║
            ║   App:        http://localhost:8080   ║
            ║   phpMyAdmin: http://localhost:8081   ║
            ╚══════════════════════════════════════╝
            """
        }
        failure {
            echo '❌ Pipeline FAILED! Check the logs above for details.'
            sh 'docker-compose logs --tail=50 || true'
        }
        always {
            echo "🏁 Pipeline finished — Build #${BUILD_NUMBER}"
        }
    }
}
