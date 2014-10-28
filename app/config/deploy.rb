set :application, "frameworksdays"
set :domain,      "144.76.238.50"
set :deploy_to,   "/var/www/frameworksdays.com"
set :app_path,    "app"

set :repository,  "git://github.com/stfalcon/fwdays.git"
set :scm,         :git

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Rails migrations will run

set :keep_releases, 3
set :user,  "frameworksdays-com"
set :use_sudo,  false

set :use_composer,  true
#set :copy_vendors,  true
#set :update_vendors,    true

set :shared_files,  ["app/config/parameters.ini"]
set :shared_children,   [app_path + "/logs", app_path + "/sessions", web_path + "/uploads"]
set :dump_assetic_assets,   true
