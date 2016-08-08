node {
    env.WORKSPACE = pwd()
    
    stage 'Code'
    checkout scm
    
    def commitSha = sho('git rev-parse --short HEAD | tee .out')
    def projectName = sho('basename `git config --get remote.origin.url | grep -o "devops.innovateuk.org.*"` | tee .out')
    projectName = projectName.substring(0, projectName.lastIndexOf('.'))
    
    stage 'Npm'
    sh "./build/steps/compile/npm.sh"
    
    stage 'Gulp'
    sh "./build/steps/compile/gulp.sh"
    
    stage 'Composer'
    sh "./build/steps/compile/composer.sh"
    
    stage 'Unit Tests'
    sh "./build/steps/test/phpunit.sh"
    
    stage 'Package'
    sh "./build/steps/compile/package.sh"
    
    def deployMethod = "rsync" //@todo load from base.properties (tarball / rsync)
    
    def packageName = "${projectName}-${commitSha}" // e.g. baseproject-e43663b
    
    if (deployMethod == ("tarball") ) {
        sh "tar -zcf ${packageName}.tar.gz -C compiled ."
        archive "${packageName}.tar.gz"
    } 
    
    remoteDeploy('integration_v3', packageName, deployMethod, false)
    // remoteDeploy('stage_brumear', packageName, deployMethod, true)
    
    if (env.BRANCH_NAME == ("master")) {
        remoteDeploy('production_degore', packageName, deployMethod, true)
    }
}
     
def remoteDeploy(String targetEnvironment, String packageName, String deployMethod, Boolean requireInput) {
   
    stage "Upload to: ${targetEnvironment}"
    
    if (requireInput) {
        input "Deploy to ${targetEnvironment}?"
    }
    
    if (deployMethod == ("tarball")) {
        sh "./build/steps/deploy/upload-by-package.sh ${targetEnvironment} ${packageName}"
    } else {
        sh "./build/steps/deploy/upload-by-rsync.sh ${targetEnvironment} ${packageName}"
    }
    
    stage "Deploy to: ${targetEnvironment}"
    sh "./build/steps/deploy/remote-deploy.sh ${targetEnvironment} ${packageName} ${deployMethod}"

    stage "Run integration tests"
    build job: 'een-integration-tests', parameters: [[$class: 'StringParameterValue', name: 'APPLICATION_ENV', value: ${targetEnvironment}]]
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