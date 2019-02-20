import requests, csv

URL = "https://data.usajobs.gov/api/Search?ResultsPerPage=500&fields=all"
CSVHEADER = ["title", "profession", "location", "salaryMin", "salaryMax", "description", "company"]
NUM_REQUESTS = 10000 / 500 # (number of total records/results per page)


def getjobs(pageNumber):
	print("Getting Page: " + str(pageNumber))
	r = requests.get(URL + "&Page=" + str(pageNumber), headers={
		"User-Agent": "lichlyts@oregonstate.edu",
		"Authorization-Key": "AwSreIf3ou/sSK0REHzwalyVPWSYZgwIQgzpD7SyNww="
	})
	result = r.json()
	return result['SearchResult']['SearchResultItems']


def initjobs():
	print("Initializing...")
	with open('usajobs.csv', 'w') as usajobs:
		writer = csv.DictWriter(usajobs, fieldnames = CSVHEADER)
		writer.writeheader()


def savejobs(jobs):
	print("Saving...")
	with open('usajobs.csv', 'a') as usajobs:
		writer = csv.DictWriter(usajobs, fieldnames = CSVHEADER)
		for job in jobs:
			job = job["MatchedObjectDescriptor"]
			title = job["PositionTitle"]
			profession = job["JobCategory"][0]["Name"]
			location = job["PositionLocation"][0]["LocationName"]
			salaryMin = job["PositionRemuneration"][0]["MinimumRange"]
			salaryMax = job["PositionRemuneration"][0]["MaximumRange"]
			description = job["UserArea"]["Details"]["MajorDuties"]
			company = job["OrganizationName"]
			
			row = {}
			row["title"] = title
			row["profession"] = profession
			row["location"] = location
			row["salaryMin"] = salaryMin
			row["salaryMax"] = salaryMax
			row["description"] = description
			row["company"] = company

			writer.writerow(row)


if __name__ == "__main__":
	initjobs()
	for page in range(1, NUM_REQUESTS):
		jobs = getjobs(page)
		savejobs(jobs)