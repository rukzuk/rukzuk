# Parse options
options = {}
options[:host_url] = ENV['RUKZUK_URL'] || "http://127.0.0.1"
options[:host_port] = ENV['RUKZUK_PORT'] || "8080"

current_dir = File.dirname(File.expand_path(__FILE__))

Vagrant.configure("2") do |config|

  # vm config
  config.vm.provider "docker" do |d|
    docker_dir = current_dir + "/tools/docker/"
    d.build_dir = docker_dir
    d.has_ssh = true
    d.volumes = [docker_dir + "volume/cms/:/srv/rukzuk/htdocs/cms:rw"]
    d.env = {
      "CMS_URL" => options[:host_url] + (options[:host_port] == "80" ? "" : ":" + options[:host_port]),
      "CMS_URL_INTERNAL" => "http://127.0.0.1",
      "SMTP_HOST" => "temail.rukzuk.intern",
      "SMTP_USER" => "user",
      "SMTP_PASSWORD" => "password",
      "SMTP_FROM" => "rukzuk@example.com"
    }
  end

  # ports
  # httpd
  config.vm.network :forwarded_port, guest:  80, host: options[:host_port]
  # php xdebug
  config.vm.network :forwarded_port, guest: 9000, host: 9009

  # folder
  config.vm.synced_folder '.', '/opt/rukzuk/htdocs'

end
