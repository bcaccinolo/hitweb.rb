# encoding: UTF-8

require 'sinatra'
require 'data_mapper'

require 'pry'

DataMapper::Logger.new($stdout, :debug)
DataMapper.setup(:default, 'mysql://root:@localhost/hitweb')

class Link
  include DataMapper::Resource

  property :id,         Serial
  property :created_at, DateTime

  property :title,       String, :length => 100
  property :url,         String
  property :description, Text

  belongs_to :category
end

class Category
  include DataMapper::Resource

  property :id,         Serial
  property :created_at, DateTime

  property :title,       String, :length => 100
  property :description, Text
  property :keywords,    String

  has n, :children,  :child_key => [ :parent_id ], :model => 'Category'
  belongs_to :parent, :model => 'Category'

  has n, :links

  def self.clean

  s = "Top/World/Français"
  puts s
  t = s.downcase
  puts t

  replacements = []
  replacements << [/ /, '-']
  replacements << [/\//, '-']

  replacements << [/[çç]/, 'c']
  replacements << [/ãåäàâá/, 'a']
  replacements << [/[éè]/, 'e']

  replacements << [/[a-z]/, '']

  replacements.each do |r|
    t = t.gsub(r[0], r[1])
  end

  puts t
    
  end
  
  def create_url 
    
    s = self.title
    
    
    c = "Top/World/Français"
    puts s
    t = s.downcase

    puts t

    replacements = []
    replacements << [/ãåäàâá/, 'a']
    replacements << [/[éè]/, 'e']
    replacements << [/Öôöó/, 'o']
    replacements << [/ìÎíîï/, 'i']
    replacements << [/üûùÜ/, 'u']
    replacements << [/ÿ/, 'y']
    replacements << [/Çç/, 'c']
    replacements << [/ñ/, 'n']
    replacements << [/ø/, 'o']
    replacements << [/Œ/, 'o']
    replacements << [/ş/, 's']
    replacements << [/ /, '-']
    replacements << [/\//, '-']

    replacements.each do |r|
      t = t.gsub(r[0], r[1])
    end

    puts t
    t
  end
end

DataMapper.finalize.auto_upgrade!

get '/' do
  haml :index, :locals => {:top => Category.first}, :layout => true
end

get '/category/:id' do
  haml :index, :locals => { :top => Category.get(params[:id]) }
end

