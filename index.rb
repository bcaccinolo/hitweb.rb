
require 'sinatra'
require 'data_mapper'

DataMapper::Logger.new($stdout, :debug)
DataMapper.setup(:default, 'mysql://root:@localhost/hitweb')

class Links
  include DataMapper::Resource

  property :id,         Serial
  property :created_at, DateTime

  property :title,       String
  property :url,         String
  property :description, Text

end

DataMapper.finalize.auto_upgrade!

get '/' do
    "Hello World!"
    stream do |out|
      out << "It's gonna be legen -\n"
      sleep 0.5
      out << " (wait for it) \n"
      sleep 1
      out << "- dary!\n"
    end
end

