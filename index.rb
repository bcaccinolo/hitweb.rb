
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
end

DataMapper.finalize.auto_upgrade!

get '/' do
  haml :index, :locals => {:top => Category.first}
end

get '/category/:id' do
  haml :index, :locals => { :top => Category.get(params[:id]) }
end

