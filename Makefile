# Makefile for building the project

app_name=inventory
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
package_name=$(app_name)

clean:
	rm -rf $(build_dir)

appstore: clean
	mkdir -p $(appstore_dir)
	tar cvzf $(appstore_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs \
	--exclude=$(project_dir)/tests \
	--exclude=$(project_dir)/.idea \
	--exclude=$(project_dir)/.editorconfig \
	--exclude=$(project_dir)/.gitattributes \
	--exclude=$(project_dir)/.gitignore \
	--exclude=$(project_dir)/.scrutinizer.yml \
	--exclude=$(project_dir)/.travis.yml \
	--exclude=$(project_dir)/build.xml \
	--exclude=$(project_dir)/CONTRIBUTING.md \
	--exclude=$(project_dir)/inventory.sublime-project \
	--exclude=$(project_dir)/inventory.sublime-workspace \
	--exclude=$(project_dir)/issue_template.md \
	--exclude=$(project_dir)/Makefile \
	--exclude=$(project_dir)/phpunit.xml \
	--exclude=$(project_dir)/README.md \
	--exclude=$(project_dir)/build \
	--exclude=$(project_dir)/img/source \
	--exclude=$(project_dir)/js/.bowerrc \
	--exclude=$(project_dir)/js/README.md \
	--exclude=$(project_dir)/js/.jshintrc \
	--exclude=$(project_dir)/js/bower.json \
	--exclude=$(project_dir)/js/Gruntfile.js \
	--exclude=$(project_dir)/js/package.json \
	--exclude=$(project_dir)/js/README.mkdir \
	--exclude=$(project_dir)/js/app \
	--exclude=$(project_dir)/js/config \
	--exclude=$(project_dir)/js/node_modules \
	--exclude=$(project_dir)/js/vendor/**/.bower.json \
	--exclude=$(project_dir)/js/vendor/**/.npmignore \
	--exclude=$(project_dir)/js/vendor/**/bower.json \
	--exclude=$(project_dir)/js/vendor/**/Gruntfile.js \
	--exclude=$(project_dir)/js/vendor/**/package.json \
	--exclude=$(project_dir)/js/vendor/**/*.md \
	--exclude=$(project_dir)/js/vendor/**/karma.conf.js \
	--exclude=$(project_dir)/js/vendor/angular-mocks
