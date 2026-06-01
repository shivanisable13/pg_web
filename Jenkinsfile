pipeline {
    agent any

    environment {

        // ============================================
        // DOCKER CONFIG
        // ============================================

        IMAGE_NAME    = "shivanisable/campusstay"

        APP_CONTAINER = "campusstay_app"
        DB_CONTAINER  = "campusstay_db"

        NETWORK_NAME  = "campusstay-network"

        // ============================================
        // DATABASE CONFIG
        // ============================================

        DB_NAME   = "campusstay"
        DB_USER   = "campusstay_user"
        DB_PASS   = "CampusStay2024"
        ROOT_PASS = "RootPass2024"
    }

    stages {

        // ============================================
        // CLONE SOURCE CODE
        // ============================================

        stage('Clone Code') {

            steps {

                git branch: 'main',
                url: 'https://github.com/shivanisable13/pg_web.git'

                sh 'ls -la'
            }
        }

        // ============================================
        // CLEAN OLD CONTAINERS
        // ============================================

        stage('Cleanup Old Containers') {

            steps {

                sh '''
                echo "Removing old containers..."

                docker rm -f $APP_CONTAINER || true
                '''
            }
        }

        // ============================================
        // CREATE NETWORK
        // ============================================

        stage('Create Network') {

            steps {

                sh '''
                docker network create $NETWORK_NAME || true
                '''
            }
        }

        // ============================================
        // START MYSQL CONTAINER
        // ============================================

        stage('Start MySQL Container') {

    steps {

        sh '''
        echo "Checking MySQL container..."

        docker start $DB_CONTAINER || docker run -d \
        --name $DB_CONTAINER \
        --network $NETWORK_NAME \
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
        // IMPORT DATABASE
        // ============================================

        stage('Import Database Schema') {

    steps {

        sh '''
        echo "Waiting additional time for DB creation..."

        sleep 20

        echo "Checking databases..."

        docker exec $DB_CONTAINER mysql \
        -u root \
        -p$ROOT_PASS \
        -e "SHOW DATABASES;"

        echo "Importing schema.sql..."

        docker exec -i $DB_CONTAINER mysql \
        -u root \
        -p$ROOT_PASS \
        < database/schema.sql

        echo "Database schema imported successfully!"
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
                echo "Starting application container..."

                docker run -d \
                --name $APP_CONTAINER \
                --network $NETWORK_NAME \
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
                echo "Performing health check..."

                sleep 10

                curl -I http://localhost || true

                echo "Running containers:"
                docker ps
                '''
            }
        }
    }

    // ============================================
    // POST BUILD ACTIONS
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

            echo '======================================'
            echo 'RUNNING CONTAINERS'
            echo '======================================'

            sh 'docker ps || true'
        }
    }
}
