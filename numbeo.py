import requests, csv
import json
import time
import numpy as np

CSVHEADER_city = ["country", "city", "state", "latitude", "city_id", "longitude"]
CSVHEADER_indexes = ["health_care_index","crime_index","pollution_index","traffic_index","quality_of_life_index","groceries_index","safety_index","city", "state","rent_index","property_price_to_income_ratio"]
CSVHEADER_prices =  ["city", "state", "average_price", "item_name", "highest_price", "item_id", "lowest_price", "data_points"]
allcitylist=[]

def getcityinfo():
	params = {
	  "api_key": "ha1vwlcog85my4",
	  "format": "csv"
	  }
	r = requests.get('https://www.numbeo.com/api/cities?api_key=ha1vwlcog85my4&country=United+States', params=params)
	data = json.loads(r.text)
	return data

def getinfo():
	params = {
	  "api_key": "ha1vwlcog85my4",
	  "format": "csv"
	  }
	count =0
	indexlist=[]
	pricelist=[]
	print("Data assessed for:")
	for city in allcitylist[:]: 
		count=count+1 
		if len(city)>0:
			if not ' ' in city:
				print(city, count)
				apiString_index = 'https://www.numbeo.com/api/indices?api_key=ha1vwlcog85my4&query='+city
				apiString_price = 'https://www.numbeo.com/api/city_prices?api_key=ha1vwlcog85my4&query='+city
			else:
				print(city, count)
				strappend= city.replace(" ", "+")
				apiString_index = 'https://www.numbeo.com/api/indices?api_key=ha1vwlcog85my4&query='+city
				apiString_price = 'https://www.numbeo.com/api/city_prices?api_key=ha1vwlcog85my4&query='+city
			r = requests.get(apiString_index, params=params)
			s = requests.get(apiString_price, params=params)
			data_index = json.loads(r.text)
			data_price = json.loads(s.text)
			if not 'error' in data_index:
				indexlist.append(data_index)
			if not 'error' in data_price:
				pricelist.append(data_price)
	return indexlist, pricelist


def splitcityname(str):
	a=''
	b=''
	garbage=''
	
	if ',' not in str:
		a=str
		b= ''
	elif str.count(',')<2:
	 	a, b = str.split(',')
	elif str.count(',')>2:
		a, b, garbage = str.split(',')
	
	allcitylist.append(a)
	
	return a, b


def savecities(data):
	with open('citylist.csv', 'r+') as citylist:
		writer = csv.DictWriter(citylist, fieldnames = CSVHEADER_city)
		writer.writeheader()
		
		for ct in data['cities']:
		 	country = ct["country"]
		 	location = ct["city"]

		 	#split city and state(and in some cases country name)
		 	city, state = splitcityname(location)

		 	# if value not present enter null
		 	if not 'latitude' in ct :
		 		latitude = ''
		 	else:
		 		latitude = ct["latitude"]

		 	if not 'city_id' in ct :
		 		city_id = ''
		 	else:
		 		city_id = ct["city_id"]

		 	if not 'longitude' in ct :
		 		longitude = ''
		 	else:
		 		longitude = ct["longitude"]

		 	row = {}
		 	row["country"] = country
		 	row["city"] = city
		 	row["state"] = state
		 	row["latitude"] = latitude
		 	row["city_id"] = city_id
		 	row["longitude"] = longitude

		 	writer.writerow(row)


def saveindices(data):
	with open('indexlist.csv', 'w+') as indexlist:
		writer = csv.DictWriter(indexlist, fieldnames = CSVHEADER_indexes)
		writer.writeheader()
		
		for ind in data[:]:
			 # if value not present enter null
		 	if not 'health_care_index' in ind :
		 		health_care_index = ''
		 	else:
		 		health_care_index = ind["health_care_index"]
			
		 	if not 'crime_index' in ind:
		 		crime_index = ''
		 	else:
		 		crime_index = ind["crime_index"]

		 	if not 'pollution_index' in ind:
		 		pollution_index = ''
		 	else:
		 		pollution_index = ind["pollution_index"]

		 	if not 'traffic_index' in ind:
		 		traffic_index = ''
		 	else:
		 		traffic_index = ind["traffic_index"]

		 	if not 'quality_of_life_index' in ind:
		 		quality_of_life_index = ''
		 	else:
		 		quality_of_life_index = ind["quality_of_life_index"]

		 	if not 'groceries_index' in ind:
		 		groceries_index = ''
		 	else:
		 		groceries_index = ind["groceries_index"]

		 	if not 'safety_index' in ind:
		 		safety_index = ''
		 	else:
		 		safety_index = ind["safety_index"]
		 	
		 	garbage=''

		 	location = ind["name"]
		 	if location.count(',')<2:
		 		city, state =  location.split(',')
		 	else:
		 		city, state, garbage =  location.split(',')

		 	if not 'rent_index' in ind:
		 		rent_index = ''
		 	else:
		 		rent_index = ind["rent_index"]

		 	if not 'property_price_to_income_ratio' in ind:
		 		property_price_to_income_ratio = ''
		 	else:
		 		property_price_to_income_ratio = ind["property_price_to_income_ratio"]

		 	row = {}
		 	row["health_care_index"] = health_care_index
		 	row["crime_index"] = crime_index
		 	row["pollution_index"] = pollution_index
		 	row["traffic_index"] = traffic_index
		 	row["quality_of_life_index"] = quality_of_life_index
		 	row["groceries_index"] = groceries_index
		 	row["safety_index"] = safety_index
		 	row["city"] = city
		 	row["state"] = state
		 	row["rent_index"] = rent_index
		 	row["property_price_to_income_ratio"] = property_price_to_income_ratio

		 	writer.writerow(row)


def saveprices(data):
	with open('cityprices.csv', 'r+') as pricelist:
		writer = csv.DictWriter(pricelist, fieldnames = CSVHEADER_prices)
		writer.writeheader()
		for ct in data[:]:

			garbage = ''
			location = ct["name"]
			
			if location.count(',')<2:
				city, state =  location.split(',')
			else:
				city, state, garbage =  location.split(',')

			for dt in ct["prices"]:

				if not 'average_price' in dt:
					average_price=''
				else:
					average_price=dt["average_price"]

				if not 'item_name' in dt:
					item_name=''
				else:
					item_name=dt["item_name"]

				if not 'highest_price' in dt:
					highest_price=''
				else:
					highest_price=dt["highest_price"]

				if not 'item_id' in dt:
					item_id=''
				else:
					item_id=dt["item_id"]

				if not 'lowest_price' in dt:
					lowest_price=''
				else:
					lowest_price=dt["lowest_price"]

				if not 'data_points' in dt:
					data_points=''
				else:
					data_points=dt["data_points"]

				row = {}
				row["city"] = city
				row["state"] = state
				row["average_price"] = average_price
				row["item_name"]= item_name
				row["highest_price"] = highest_price
				row["item_id"]= item_id
				row["lowest_price"]= highest_price
				row["data_points"]= data_points
				writer.writerow(row)


if __name__ == "__main__":
	start_time = time.time()
	cities = getcityinfo()
	savecities(cities)
	indices, prices = getinfo()
	saveindices(indices)
	saveprices(prices)
	print("--- %s seconds ---" % (time.time() - start_time))

