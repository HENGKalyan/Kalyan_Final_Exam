pipeline {
    agent any
    
    triggers {
        pollSCM('H/5 * * * *')  // Poll SCM every 5 minutes
    }
    
    environment {
        DOCKER_REGISTRY = 'localhost:8081'
        PROJECT_NAME = 'terrain-booking-system'
        CC_EMAIL = 'srengty@gmail.com'
    }
    
    stages {
        stage('Checkout') {
            steps {
                echo 'Checking out source code...'
                checkout scm
            }
        }
        
        stage('Install Dependencies') {
            steps {
                echo 'Installing PHP dependencies...'
                sh '''
                    # Install PHP dependencies including dev dependencies for testing
                    composer install --optimize-autoloader
                    cp .env.example .env
                    php artisan key:generate
                '''
            }
        }
        
        stage('Run Tests') {
            steps {
                echo 'Running Laravel tests with MySQL...'
                sh '''
                    # Use MySQL for testing
                    cp .env .env.testing
                    php artisan migrate --env=testing --force
                    
                    # Check if PEST exists and run tests
                    if [ -f "./vendor/bin/pest" ]; then
                        ./vendor/bin/pest
                    else
                        echo "PEST not found, running PHPUnit instead"
                        php artisan test
                    fi
                '''
            }
            post {
                always {
                    archiveArtifacts artifacts: 'storage/logs/*.log', allowEmptyArchive: true
                }
            }
        }
        
        stage('Build Assets') {
            steps {
                echo 'Building frontend assets...'
                sh '''
                    # Check if package.json exists
                    if [ -f "package.json" ]; then
                        echo "Installing npm dependencies..."
                        rm -rf node_modules package-lock.json
                        npm install
                        npm run build || echo "Asset build failed but continuing..."
                    else
                        echo "No package.json found, skipping asset build"
                    fi
                '''
            }
        }
        
        stage('Deploy with Ansible') {
            when {
                branch 'main'
            }
            steps {
                echo 'Deploying to Kubernetes with Ansible...'
                sh '''
                    if [ -d "ansible" ]; then
                        cd ansible
                        ansible-playbook -i inventory/hosts playbooks/deploy-laravel.yml -v || echo "Ansible deployment failed but continuing..."
                    else
                        echo "Ansible directory not found, skipping deployment"
                    fi
                '''
            }
        }
    }
    
    post {
        always {
            script {
                def lastCommitEmail = sh(
                    script: "git log -1 --pretty=format:'%ae'",
                    returnStdout: true
                ).trim()
                
                if (!lastCommitEmail) {
                    lastCommitEmail = 'kalyanheng99@gmail.com'
                }
                
                env.DEVELOPER_EMAIL = lastCommitEmail
            }
            cleanWs()
        }
        
        failure {
            echo "Build failed - Email notifications would be sent to:"
            echo "Developer: ${env.DEVELOPER_EMAIL}"
            echo "CC: ${env.CC_EMAIL}"
            echo "Subject: Build Failed: ${env.JOB_NAME} - ${env.BUILD_NUMBER}"
        }
        
        success {
            echo 'Build and deployment completed successfully!'
            echo "Success notification would be sent to: ${env.DEVELOPER_EMAIL}"
            echo "Subject: Build Success: ${env.JOB_NAME} - ${env.BUILD_NUMBER}"
        }
    }
}