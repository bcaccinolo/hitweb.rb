require "bundler/capistrano"

set :application, "hitweb-rb"

set :scm, :git
set :repository, "git://github.com/bcaccinolo/hitweb.rb.git"
set :deploy_via, :remote_cache # do not a full clone on every deploy

set :deploy_to, "/var/www/hitweb-rb"

server "66dating.com", :app, :web, :db, :primary => true

default_run_options[:pty] = true

# if you want to clean up old releases on each deploy uncomment this:
after "deploy:restart", "deploy:cleanup"

# If you are using Passenger mod_rails uncomment this:
namespace :deploy do
  task :start do ; end
  task :stop do ; end
  task :restart, :roles => :app, :except => { :no_release => true } do
    run "#{try_sudo} touch #{File.join(current_path,'tmp','restart.txt')}"
  end
end
