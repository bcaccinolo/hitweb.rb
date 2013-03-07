# encoding: UTF-8

require 'sinatra'
require 'data_mapper'
require 'cgi'
# require 'pry'

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

  property :title,       String, :length => 300
  property :url,       String, :length => 300
  property :description, Text
  property :keywords,    String

  has n, :children,  :child_key => [ :parent_id ], :model => 'Category'
  belongs_to :parent, :model => 'Category'

  has n, :links

  def title_simplification
    self.title = self.title.gsub(/Top\/World\/Français\//,'')
    self.save
  end

  def generate_url

    s = self.title
    t = s.downcase

    replacements = []

    # special chars
    replacements << [/[°]/, '']
    replacements << [/[\/_\-,'&\.\ ́’\+ ]/, '-']

    #special letters
    replacements << [/[ççÇ]/, 'c']
    replacements << [/[ãåäàâá]/, 'a']
    replacements << [/[Ééèêë]/, 'e']
    replacements << [/[œôÖöóø]/, 'o']
    replacements << [/[Îîïíì]/, 'i']
    replacements << [/[Üüûù]/, 'u']
    replacements << [/[ÿ]/, 'y']
    replacements << [/[şŠ]/, 's']
    replacements << [/[ñ]/, 'n']

    replacements << [/--/, '-'] # 2 '-' > 1 '-'

    # replacements << [/[a-z0-9]/, '']

    replacements.each do |r|
      t = t.gsub(r[0], r[1])
    end

    self.url = t
    self.save

    return t
  end

end

# DataMapper.finalize.auto_upgrade!

get '/' do
  unless params['categories_parents_id'].nil?
    res = Category.all(id:params['categories_parents_id'])
    if res.size >= 1
      r = res.first
      haml :index, :locals => { :top => r }
    end
  else
    haml :index, :locals => {:top => Category.first}, :layout => true
  end
end


# to handle '/index.php?categories_parents_id=:cat_id'
get '/index.php' do
  res = Category.all(id:params['categories_parents_id'])
  if res.size >= 1
    r = res.first
    haml :index, :locals => { :top => r }
  end
end

get '/category/:id' do
  haml :index, :locals => { :top => Category.get(params[:id]) }
end

get '/sitemap.xml' do
  builder :sitemap
end

get '/:cat_url' do
  res = Category.all(url:params['cat_url'])
  if res.size >= 1
    r = res.first
    haml :index, :locals => { :top => r }
  end
end

