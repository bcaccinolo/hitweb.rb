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
  require 'pry';binding.pry ;
  haml :index, :locals => {:top => Category.first}, :layout => true
end

get '/category/:id' do
  haml :index, :locals => { :top => Category.get(params[:id]) }
end


cs = Category.all
count = 0
cs.each do |c|
  puts c.title
  puts c.generate_url
  puts c.url
end
exit

#   puts c.title
#   puts '########'
  # t = c.url
  # puts t 
  # if t.size > 0
  #   puts c.title
  #   puts "  #{t}"
  #   puts "####################"
  #   count += 1
  # end
  # if count > 10
  #   break
  # end
# end
# exit





