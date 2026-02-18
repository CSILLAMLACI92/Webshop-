import urllib.request,urllib.parse,re  
url='https://www.thomann.de/hu/search.html'  
params={'sw':'elektromos gitar','view':'grid'}  
full=url+'?'+urllib.parse.urlencode(params)  
req=urllib.request.Request(full,headers={'User-Agent':'Mozilla/5.0'})  
html=urllib.request.urlopen(req,timeout=30).read().decode('utf-8','ignore')  
