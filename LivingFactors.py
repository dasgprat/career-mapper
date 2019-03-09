import urllib
import requests
from bs4 import BeautifulSoup
import codecs
import re
import itertools



def statename(soup):
    randomtext = soup.find_all('p', class_="p-text")
    mylist = []
    for i in range(5,len(randomtext)):
        check = str(randomtext[i])[26:28]
        pattern = re.compile("^([0-9].)")
        if pattern.match(check):
            mylist.append(randomtext[i].find_all('strong'))
        myliststates = list(itertools.chain.from_iterable(mylist))
    return myliststates


def CostofLiving(soup):
    mylist = []
    content = soup.find_all('li')
    #print content
    for i in range(0,len(content)):
        checkCost = str(content[i])[4:9]
        pattern = re.compile("^(Cost.)")
        if pattern.match(checkCost):
            mylist.append(content[i])
            #print "mango"
        mylistCostofLiving = list(itertools.chain.from_iterable(mylist))
    return mylistCostofLiving
    

def MedianHouseValue(soup):
    mylist = []
    content = soup.find_all('li')
    #print content
    for i in range(0,len(content)):
        checkHome = str(content[i])[4:11]
        pattern = re.compile("^(Median.)")
        if pattern.match(checkHome):
            mylist.append(content[i])
            #print "mango"
        mylistHomeValue = list(itertools.chain.from_iterable(mylist))
    return mylistHomeValue
    

source = requests.get("https://www.usatoday.com/story/money/economy/2018/05/10/cost-of-living-value-of-dollar-in-every-state/34567549/")
#print page.status_code
soup = BeautifulSoup(source.content, 'html.parser')
soup.prettify()
soup = BeautifulSoup(source.content, 'html.parser')

'''statenameList = statename(soup)

f= open("fo.txt","w+")
f.write(str(statenametextP))
f.close()'''

CostofLivingList = CostofLiving(soup)

HouseValue = MedianHouseValue(soup)
 
#print contentTextDiv

f= open("foCostofLiving.txt","w+")
f.write(str(CostofLivingList))
f.close()

f= open("foHomeValue.txt","w+")
f.write(str(HouseValue))
f.close()