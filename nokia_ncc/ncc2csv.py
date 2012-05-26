# -*- coding: UTF-8 -*-

############################################################
# Convert Nokia NCC file to CSV for Import to Thunderbird  #
#   written 2011 by Jan Rauschenbach                       #
#   contact: coding@jan-rauschenbach.de                    #
############################################################

import sys

IN_FILE = "Verzeichnis.ncc"
OUT_FILE = "Kontakte.csv"


# field types
fields = {
	'202': "Name",
	'204': "Notiz",
	'205': "E-Mail",
	'206': "Adresse",
	'208': "Tel. Geschäft", #"Tel. Allgemein", (mapped to business, becaue thunderbird has no field for default number)
	'209': "Tel. Privat",
	'210': "Tel. Mobil",
	'211': "Fax",
	'213': "Tel. Geschäft",
	'219': "Tel. Geschäft", #"Tel. Allgemein", (mapped to business, becaue thunderbird has no field for default number)
	#'': "",
}

# ask for files
print("="*70)
sInfile = input("".join(["Name der NCC-Datei (Standard: '", IN_FILE, "'): "]))
if sInfile != "": IN_FILE = sInfile
print()
sOutfile = input("".join(["Name der CSV-Datei (Standard: '", OUT_FILE, "'): "]))
if sOutfile != "": OUT_FILE = sOutfile
print("="*70)

# open files
try:
	fIn = open(IN_FILE, mode='r', encoding='utf-16')
except IOError:
	print("FEHLER: Konnte NCC-Datei (", IN_FILE, ") nicht öffnen.", sep='')
	sys.exit()
try:
	fOut = open(OUT_FILE, mode='w', encoding='utf-8')
except IOError:
	print("FEHLER: Konnte CSV-Datei (", OUT_FILE, ") nicht öffnen.", sep='')
	sys.exit()

# go trough file an fill dict
contacts = {}
lines = 0
numbers = {}
doubles = {}
double_numbers = []
for line in fIn:
	lines += 1
	if line[0:3] == "200" or line[0:3] == "225":
		segments = line[:-1].split("	")
		contact = {
			"Name": "",
			"Notiz": "",
			"E-Mail": "",
			"Adresse": "",
			"Tel. Geschäft": "",
			"Tel. Privat": "",
			"Tel. Mobil": "",
			"Fax": ""
		}
		index = 2
		if segments[0] == "200" and segments[1] == "PIT_CONTACT":
			contact["SIM"] = False
		elif segments[0] == "225" and segments[1] == "PIT_CONTACT_SIM":
			contact["SIM"] = True
		else:
			print("FEHLER: Ungültiger Eintrag (Zeile ", lines, ")", sep='')
			continue
		while index < len(segments):
			if segments[index] in fields.keys():
				# normalize numbers
				if segments[index+1][0] == "0":
					if segments[index+1][1] == "0":
						segments[index+1] = "+" + segments[index+1][2:]
					else:
						segments[index+1] = "+49" + segments[index+1][1:]
				# search for doubles
				if contact["Name"] != "":
					if segments[index+1] not in numbers.keys():
						numbers[segments[index+1]] = contact["Name"]
					else:
						doubles[contact["Name"]] = numbers[segments[index+1]]
						double_numbers.append(segments[index+1])
				# if field already has number, store as unknown
				if contact[fields[segments[index]]] != "":
					if "Unbekannt" not in contact:
						contact["Unbekannt"] = []
					contact["Unbekannt"].append(segments[index+1])
				else:
					contact[fields[segments[index]]] = segments[index+1]
			else:
				print("WARNUNG: Unbekanntes Feld '", segments[index], "' (Zeile ", lines, ")", sep='')
			index += 2
		contacts[contact["Name"]] = contact
print(len(contacts), "Kontakte in", lines, "Zeilen gefunden")
print()

# handle doubles
for double in doubles:
	for field in contacts[double]:
		if field != "Name" and field != "SIM":
			if contacts[double][field] != "":
				if contacts[doubles[double]][field] == "":
					contacts[doubles[double]][field] = contacts[double][field]
				elif contacts[doubles[double]][field] not in double_numbers:
					if "Unbekannt" not in contacts[doubles[double]]:
						contacts[doubles[double]]["Unbekannt"] = []
					contacts[doubles[double]]["Unbekannt"].append(contacts[doubles[double]][field])
	contacts.pop(double)

# handle unknown types
for name in contacts:
	contact = contacts[name]
	if "Unbekannt" in contact:
		print("Zu Kontakt", contact["Name"], "konnten nicht alle Felder zugeordnet werden:")
		for val in contact["Unbekannt"]:
			for field in contact:
				if field != "Unbekannt":
					if contact[field] != "":
						print("    ", field, ": ", contact[field], sep='')
			print()
			print("Nicht zugeordneter Wert:", val)
			print("                        ", "-"*20)
			print("Zuordnen: [1]E-Mail  [2]Tel. Geschäft  [3]Tel. Privat  [4]Tel. Mobil")
			print("          [5]Fax     [6]Adresse        [7]Notiz        [8]an Notizen anhängen")
			iInput = -1
			while iInput < 0 or iInput > 8:
				sInput = input("Bitte wählen (0 für ignorieren): ")
				try:
					iInput = int(sInput)
				except ValueError:
					iInput = -1
			if iInput == 1:
				contact["E-Mail"] = val
			elif iInput == 2:
				contact["Tel. Geschäft"] = val
			elif iInput == 3:
				contact["Tel. Privat"] = val
			elif iInput == 4:
				contact["Tel. Mobil"] = val
			elif iInput == 5:
				contact["Fax"] = val
			elif iInput == 6:
				contact["Adresse"] = val
			elif iInput == 7:
				contact["Notiz"] = val
			elif iInput == 8:
				contact["Notiz"] += "".join([", ", val])
			print("-"*70)

# write output file
fOut.write("Anzeigename	E-Mail-Adresse	Tel. dienstlich	Tel. privat	Fax-Nummer	Mobil-Tel.-Nr.	Adresse	Notiz\n")
for contact in contacts:
	fOut.write("".join([
		contacts[contact]["Name"], "	",
		contacts[contact]["E-Mail"], "	",
		contacts[contact]["Tel. Geschäft"], "	",
		contacts[contact]["Tel. Privat"], "	",
		contacts[contact]["Fax"], "	",
		contacts[contact]["Tel. Mobil"], "	",
		contacts[contact]["Adresse"], "	",
		contacts[contact]["Notiz"], "\n",
	]))

fIn.close()
fOut.close() 