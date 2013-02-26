host='http://francemoz.fr'

xml.instruct!
xml.urlset "xmlns" => "http://www.sitemaps.org/schemas/sitemap/0.9" do

  xml.url do
    xml.loc host
    xml.priority 1.0
  end

  Category.all.each do |cat|
    xml.url do
      xml.loc "#{host}/#{cat.url}"
      xml.priority 0.9
    end
  end

end
