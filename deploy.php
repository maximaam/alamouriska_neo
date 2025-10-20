<?php
namespace Deployer;

require 'recipe/common.php';
require 'recipe/symfony.php';

// -------------------------------
// Project repository
// -------------------------------
set('repository', 'git@github.com:maximaam/alamouriska_neo.git');
set('branch', 'main');

// -------------------------------
// Remote server configuration
// -------------------------------
host('strato')
    ->set('deploy_path', '~/alamouriska_neo');

// -------------------------------
// Keep last 3 releases
// -------------------------------
set('keep_releases', 3);

// -------------------------------
// Shared files/dirs
// -------------------------------
set('shared_files', ['.env.local']);
set('shared_dirs', ['var/log']);
set('writable_dirs', ['var']);
set('writable_mode', 'chmod');
set('http_user', '1260468');
set('bin/php', '/opt/RZphp83/bin/php-cli');
set('bin/composer', '{{bin/php}} /mnt/web319/b1/37/51912237/htdocs/bin/composer');
set('bin/console', '{{release_path}}/bin/console');
set('php', '/opt/RZphp83/bin/php-cli');

// -------------------------------
// Force Git to use your SSH key
// -------------------------------
set('git_env', [
    'GIT_SSH_COMMAND' => 'ssh -i /mnt/web319/b1/37/51912237/htdocs/.ssh/github_deploy_key -o StrictHostKeyChecking=no'
]);
set('git_tty', true); // required for some SSH setups

// -------------------------------
// Override update_code to use bash -lc
// -------------------------------
task('deploy:update_code', function () {
    $releasePath = get('release_path');
    $repository  = get('repository');
    $branch      = get('branch', 'main');

    // Make sure the release folder exists and is empty
    run("mkdir -p $releasePath && rm -rf $releasePath/*");

    // Clone the repository **directly into the release folder**
    run("bash -lc 'GIT_SSH_COMMAND=\"ssh -i /mnt/web319/b1/37/51912237/htdocs/.ssh/github_deploy_key -o StrictHostKeyChecking=no\" git clone -b $branch --recursive $repository $releasePath'");
});

// -------------------------------
// Optional: install Symfony assets
// -------------------------------
/*
task('deploy:assets', function () {
    run('cd {{release_path}} && php bin/console assets:install --symlink');
});
*/

// Optional: run migrations
/*
task('database:migrate', function () {
    run('cd {{release_path}} && php bin/console doctrine:migrations:migrate --no-interaction');
});
*/

// -------------------------------
// Deploy flow
// -------------------------------
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:cache:clear',
    'deploy:publish',
]);


// -------------------------------
// Copy latest releass to app folder, so that strato can see it 
// Strato does not list symlinked folders in the domain redirect dropdown list
// -------------------------------
task('deploy:publish_app', function () {
    $releasePath = get('release_path');
    run('rm -rf ~/alamouriska_neo/app/*');
    run("cp -a $releasePath/. ~/alamouriska_neo/app/");
    run("ln -sf ~/alamouriska_neo/shared/.env.local ~/alamouriska_neo/app/.env.local");
});
after('deploy:symlink', 'deploy:publish_app');

after('deploy:failed', 'deploy:unlock');

// Optional: add assets and migrations
// after('deploy:cache:clear', 'database:migrate');
// after('deploy:failed', 'deploy:unlock');

