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
                    # Install PHP dependencies
                    composer install --no-dev --optimize-autoloader
                    cp .env.example .env
                    php artisan key:generate
                '''
            }
        }
        
        stage('Run Tests') {
            steps {
                echo 'Running Laravel tests with MySQL (production DB)...'
                sh '''
                    # Use MySQL for testing (same as production)
                    cp .env .env.testing
                    php artisan migrate --env=testing --force
                    ./vendor/bin/pest
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
                    npm install --no-optional
                    npm run build
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
                    cd ansible
                    ansible-playbook -i inventory/hosts playbooks/deploy-laravel.yml -v
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
            echo "Build failed - Email notifications would be sent to: ${env.DEVELOPER_EMAIL}, ${env.CC_EMAIL}"
        }
        
        success {
            echo 'Build and deployment completed successfully!'
            echo "Success notification would be sent to: ${env.DEVELOPER_EMAIL}"
        }
    }
}