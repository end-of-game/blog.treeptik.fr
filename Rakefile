require 'date'

desc "Builds everything"
task :default => [:clean, :sass, :jekyll]

desc "Builds Jekyll in a clean environment"
task :'jekyll-clean' => [:clean, :jekyll]

desc "Builds all SASS scripts"
task :sass do
  sh 'sass', '-C', '-f', '-t', 'compact', '--update', '_sass:css'
end

desc "Compiles site with Jekyll for testing"
task :jekyll do
  sh 'jekyll', '--pygments', '--safe'
end

desc "Cleans out the old site"
task :clean do
  sh 'rm', '-rf', '_site'
end

desc "Create a new post usage rake post 'Title' 'tags,super tags' 'author'"
task :post do 
  title = ARGV[1]
  tags = ARGV[2]
  author = ARGV[3]

  title || begin
     puts "Usage: You must specify a title: rake post'[Hello world!]'"
     return 1
  end

  # Sanitize our title and generate other data
  safe_title = title.downcase.gsub(/[^a-z0-9]+/, '-').squeeze('-').gsub(/\A-+|-+\z/, '')
  now = DateTime.now
  date = now.strftime '%Y-%m-%d'
  file = "_posts/#{date}-#{safe_title}.markdown"

  # Generate tags 
  tagsStr = ""
  tags.split(',').each do |tag|
    tagsStr << "- #{tag}\r\n"  
  end 

  # Actually generate the new branch and file
  git "checkout", "-b", "drafts/#{safe_title}", "master"
  sh 'mkdir', '_posts' if !File.exists? '_posts'
  sh "touch", file
  git "add", "-N", file
  puts "Writing headers..."
  File.open(file, 'w') do |draft|
    draft.puts <<TEMPLATE
---
title: #{title}
date: #{now.strftime '%Y-%m-%d %H:%I:%S %z'}
layout: post
author: #{author}
tags:
#{tagsStr}
---

TEMPLATE
  end

  # Open the file for editing. At this point, we are done so exec out so
  # rake can't whine too much if we screw up our editor
  editor = ENV['EDITOR'] || 'vi'
  exec editor, file
end

desc "Publishes the currently checked out draft branch to master"
task :publish do
  branch = git_branch
  if branch.match(/^drafts\//).nil?
    puts "You must be on at the head of a draft branch."
    return 1
  end
  if !`git diff --cached --name-only`.strip.empty?
    puts "You must be on a clean checkout, or at least have an empty index."
    return 1
  end
  title = branch.gsub(/^drafts\//, '')
  git 'checkout', 'master'
  git 'merge', '--squash', branch
  git 'commit', '-m', "Publishing '#{title}'"
  git 'branch', '-D', branch
end

def git(*args)
  sh 'git', *args
end

def git_branch
  branch = `git branch -a 2> /dev/null | grep "^* " | awk '{print $2}'`.strip
  if branch == '(no branch)' || !branch
    branch = nil
  end
  branch
end
