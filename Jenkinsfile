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
                    composer install --no-dev --optimize-autoloader
                    cp .env.example .env
                    php artisan key:generate
                '''
            }
        }

        stage('Run Tests') {
            steps {
                echo 'Running Laravel tests with SQLite...'
                sh '''
                    # Configure for SQLite testing
                    cp .env .env.testing
                    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env.testing
                    touch database/test.sqlite

                    # Run migrations and tests
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
                    npm install
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
            emailext (
                subject: "Build Failed: ${env.JOB_NAME} - ${env.BUILD_NUMBER}",
                body: """
                Build failed for ${env.JOB_NAME} - Build ${env.BUILD_NUMBER}

                Build URL: ${env.BUILD_URL}
                Git Commit: ${env.GIT_COMMIT}

                Please check the Jenkins console output for details.
                """,
                to: "${env.DEVELOPER_EMAIL}, ${env.CC_EMAIL}",
                from: "jenkins@devops-exam.local"
            )
        }

        success {
            echo 'Build and deployment completed successfully!'
            emailext (
                subject: "Build Success: ${env.JOB_NAME} - ${env.BUILD_NUMBER}",
                body: """
                Build completed successfully for ${env.JOB_NAME} - Build ${env.BUILD_NUMBER}

                Application deployed successfully to Kubernetes cluster.
                """,
                to: "${env.DEVELOPER_EMAIL}",
                from: "jenkins@devops-exam.local"
            )
        }
    }
}
