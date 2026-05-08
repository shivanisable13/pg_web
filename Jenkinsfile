pipeline {
    agent any

    environment {
        IMAGE_NAME    = "shivanisable/campusstay"
        APP_CONTAINER = "campusstay-app"
        DB_CONTAINER  = "campusstay-db"
        DB_NAME       = "campusstay"
        DB_USER       = "campusstay_user"
        DB_PASS       = "CampusStay2024"
        ROOT_PASS     = "RootPass2024"
    }

    stages {

        // ============================================
        // CLONE SOURCE CODE
        // ============================================
        stage('Clone Code') {
            steps {

                git branch: 'main',
                url: 'https://github.com/YOUR_USERNAME/YOUR_REPO.git'

                sh 'ls -la'
            }
        }

        // ============================================
        // REMOVE OLD APP CONTAINER
        // ============================================
        stage('Cleanup Old App Container') {
            steps {

                sh '''
                docker rm -f $APP_CONTAINER || true
                '''
            }
        }

        // ============================================
        // CREATE DOCKER NETWORK
        // ============================================
        stage('Create Network') {
            steps {

                sh '''
                docker network create campusstay-network || true
                '''
            }
        }

        // ============================================
        // START MYSQL CONTAINER
        // ============================================
        stage('Start MySQL Container') {
            steps {

                sh '''
                docker start $DB_CONTAINER || docker run -d \
                --name $DB_CONTAINER \
                --network campusstay-network \
                -v campusstay_mysql_data:/var/lib/mysql \
                -e MYSQL_ROOT_PASSWORD=$ROOT_PASS \
                -e MYSQL_DATABASE=$DB_NAME \
                -e MYSQL_USER=$DB_USER \
                -e MYSQL_PASSWORD=$DB_PASS \
                -p 3307:3306 \
                mysql:8.0
                '''
            }
        }

        // ============================================
        // WAIT FOR MYSQL
        // ============================================
        stage('Wait For MySQL') {
            steps {

                sh '''
                echo "Waiting for MySQL..."

                until docker exec $DB_CONTAINER mysqladmin ping \
                    -h localhost \
                    -u root \
                    -p$ROOT_PASS --silent
                do
                    echo "MySQL is starting..."
                    sleep 2
                done

                echo "MySQL is ready!"
                '''
            }
        }

        // ============================================
        // IMPORT DATABASE SCHEMA
        // ============================================
        stage('Import Database Schema') {
            steps {

                sh '''
                echo "Importing schema.sql..."

                docker exec -i $DB_CONTAINER mysql \
                    -u root \
                    -p$ROOT_PASS \
                    $DB_NAME < database/schema.sql

                echo "Database imported successfully!"
                '''
            }
        }

        // ============================================
        // BUILD APPLICATION IMAGE
        // ============================================
        stage('Build App Image') {
            steps {

                sh '''
                docker build -t $IMAGE_NAME .
                '''
            }
        }

        // ============================================
        // RUN APPLICATION CONTAINER
        // ============================================
        stage('Run App Container') {
            steps {

                sh '''
                docker rm -f $APP_CONTAINER || true

                docker run -d \
                --name $APP_CONTAINER \
                --network campusstay-network \
                -v campusstay_uploads:/var/www/html/uploads \
                -p 80:80 \
                -e DB_HOST=$DB_CONTAINER \
                -e DB_NAME=$DB_NAME \
                -e DB_USER=$DB_USER \
                -e DB_PASS=$DB_PASS \
                $IMAGE_NAME
                '''
            }
        }

        // ============================================
        // HEALTH CHECK
        // ============================================
        stage('Health Check') {
            steps {

                sh '''
                sleep 10

                curl -I http://localhost || true

                docker ps
                '''
            }
        }
    }

    // ============================================
    // POST BUILD
    // ============================================
    post {

        success {

            echo '======================================'
            echo 'DEPLOYMENT SUCCESSFUL'
            echo '======================================'

            echo 'Application URL:'
            echo 'http://YOUR_EC2_PUBLIC_IP'
        }

        failure {

            echo '======================================'
            echo 'DEPLOYMENT FAILED'
            echo '======================================'

            sh 'docker logs $APP_CONTAINER || true'

            sh 'docker logs $DB_CONTAINER || true'
        }

        always {

            sh 'docker ps || true'
        }
    }
}
