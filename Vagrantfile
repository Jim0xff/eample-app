# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "forwarded_port", guest: 80, host: 8001
  #config.vm.network "forwarded_port", guest: 6379, host: 6379
  config.vm.network "private_network", ip: "192.168.222.10"
  config.vm.synced_folder ".", "/vagrant"

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1024"
  end

#   config.vm.provision "ansible" do |ansible|
#     ansible.playbook = "provisioning/vagrant.yml"
#     #ansible.verbose = "vvvv"
#   end

  # https://github.com/mitchellh/vagrant/issues/8016
  # There is a bug cause run: :never won't work
  # Just comment it instead
  # config.vm.provision "production-test", type: "ansible", run: "never" do |ansible|
  #   ansible.playbook = "provisioning/vagrant-production-test.yml"
  #   ansible.ask_vault_pass = true
  #   ansible.verbose = "vvvv"
  # end

  # config.vm.provision "front-end-test", type: "ansible", run: "never" do |ansible|
  #   ansible.playbook = "provisioning/vagrant-front-end-test.yml"
  #   ansible.verbose = "vvvv"
  # end

end
