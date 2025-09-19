module.exports = function(grunt) {

  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),
    paths: {
      root: '../',
      resources: '<%= paths.root %>Resources/',
      vendor: '<%= paths.resources %>Public/Vendor/'
    },
    compress: {
      jquery: {
        options: {
          mode: 'gzip',
          level: 9
        },
        expand: true,
        cwd: '<%= paths.vendor %>',
        src: ['**/*.js'],
        dest: '<%= paths.vendor %>',
        ext: '.js.gz',
        filter: 'isFile',
        extDot: 'last'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.registerTask('default', ['compress:jquery']);
};
