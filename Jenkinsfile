node {
    env.WORKSPACE = pwd()
    
    stage 'Code'
    checkout scm
    
    def commitSha = sho('git rev-parse --short HEAD | tee .out')
    def projectName = sho('basename `git config --get remote.origin.url | grep -o "devops.innovateuk.org.*"` | tee .out')
    projectName = projectName.substring(0, projectName.lastIndexOf('.'))
    
    // clone credentials repo and inject into codebase
    sh "./build/steps/compile/credentials.sh"	
    
    stage 'Npm'
    sh "./build/steps/compile/npm.sh"
    
    stage 'Gulp'
    sh "./build/steps/compile/gulp.sh"
    
    stage 'Composer'
    sh "./build/steps/compile/composer.sh"
    
    stage 'Unit Tests'
    sh "./build/steps/compile/phpunit.sh"
    
    stage 'Package'
    sh "./build/steps/compile/package.sh"
    
    def deployMethod = "rsync" //@todo load from base.properties (tarball / rsync)
    
    def packageName = "${projectName}-${commitSha}" // e.g. baseproject-e43663b
    
    if (deployMethod == ("tarball") ) {
        sh "tar -zcf ${packageName}.tar.gz -C compiled ."
        archive "${packageName}.tar.gz"
    } 
    
    remoteDeploy('integration_v3', packageName, deployMethod, false, false, false)
    remoteDeploy('stage_een_aws', packageName, deployMethod, true, true, false)
    
    if (env.BRANCH_NAME == ("master")) {
        remoteDeploy('production_een_aws', packageName, deployMethod, true, true, false)
    }
}
     
def remoteDeploy(String targetEnvironment, String packageName, String deployMethod, Boolean requireInput, Boolean updateAmi, Boolean tests) {
   
    stage "Upload to: ${targetEnvironment}"
    
    deploy = false
    if (requireInput) {
        try {
            timeout(time: 120, unit: 'SECONDS') {
        input "Deploy to ${targetEnvironment}?"
                deploy = true
            }
        } catch(err) {
            deploy = false
        }
    } else {
        deploy = true
    }
    
    if (deploy == true) {
        if (deployMethod == ("tarball")) {
            sh "./build/steps/deploy/upload-by-package.sh ${targetEnvironment} ${packageName}"
        } else {
            sh "./build/steps/deploy/upload-by-rsync.sh ${targetEnvironment} ${packageName}"
        }




        stage "Deploy to: ${targetEnvironment}"


            try {
                timeout(time: 10, unit: 'SECONDS') {
                    reloadConfiguration = input message: 'Reload Drupal config?', parameters: [[$class: 'BooleanParameterDefinition', defaultValue: false, description: '', name: 'yes']]
                }
            } catch (err) {
                reloadConfiguration = false
            }

            rebuildDatabase = false
        
            sh "./build/steps/deploy/deploy-on-remote.sh ${targetEnvironment} ${packageName} ${deployMethod} ${rebuildDatabase} ${reloadConfiguration}"
        
        stage "Integration tests: ${targetEnvironment}"
        build job: 'een-integration-tests', parameters: [[$class: 'StringParameterValue', name: 'APPLICATION_ENV', value: "${targetEnvironment}"]], propagate: false

        if (updateAmi) {
            stage "Update AMI: ${targetEnvironment}"
            sh "export APPLICATION_ENV=${targetEnvironment} && ./build/steps/post-build/update-ami.sh ${targetEnvironment} ${packageName}"
        }
    }
}

// sh-out - return the output from an sh command    
String sho(String cmd) {
    sh cmd
    String out = readFile('.out').trim()
    if (out) {
        return out
    }
    error "failed to run: $cmd"
}
