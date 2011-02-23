#!/usr/bin/python

from googlevoice import Voice,util
from BeautifulSoup import BeautifulSoup
import urllib2
import urllib
import re
import time
import string

while True:
	voice = Voice()
	print "logging in..."
	voice.login('***gmail****', ******'gmail password******')
	newMessage=False
	voiceRequest=False
	smsRequest=False
        if len(voice.sms().messages)>0:
		newMessage=True
		print "woohoo!  sms!"
		sms_parse=BeautifulSoup(voice.sms_html())
		extractedText=sms_parse.find(attrs={"class":"gc-message-sms-text"});
		extractedSMSNumber=sms_parse.find(attrs={"class":"gc-message-sms-from"});
		HTMLtag = re.compile('<.*?>')      # Matches HTML tags
		HTMLcom = re.compile('<!--.*?-->') # Matches HTML comments
      		extractedText = HTMLtag.sub('', HTMLcom.sub('', str(extractedText)))
      		extractedSMSNumber = HTMLtag.sub('', HTMLcom.sub('', str(extractedSMSNumber)))
		extractedSMSNumber=extractedSMSNumber.strip();
		extractedSMSNumber=extractedSMSNumber.replace(":","");
		extractedSMSNumber=extractedSMSNumber.replace("(","");
		extractedSMSNumber=extractedSMSNumber.replace(")","");
		extractedSMSNumber=extractedSMSNumber.replace("-","");
		extractedSMSNumber=extractedSMSNumber.replace(" ","");
	#	print  extractedText
	#	print  extractedSMSNumber
		message_text=extractedText
		number=extractedSMSNumber
		smsRequest=True

	if len(voice.voicemail().messages) > 0 and smsRequest==False:
		newMessage=True
		voiceRequest=True
		print "wa wa wee wa!  We got a message!"
       	#Take a peek into the voicemail folder
		voice_parse=BeautifulSoup(voice.voicemail_html())		
#              	print(voice.voicemail_html())
       	#Search for voicemail messages
		voice_content=voice_parse.find(attrs={"class":"gc-message-message-display"})
		extractednumber=voice_parse.find(attrs={"class":"gc-message-mni"})
       	# Strip out the HTML 
		HTMLtag = re.compile('<.*?>')      # Matches HTML tags
		HTMLcom = re.compile('<!--.*?-->') # Matches HTML comments
		voice_clean = HTMLtag.sub('', HTMLcom.sub('', str(voice_content)))
		print extractednumber
		if extractednumber is not None:
			number=HTMLtag.sub('',HTMLcom.sub('', str(extractednumber)))
		else:
			number=None
		message_text = voice_clean.replace("\n"," ")
		message_text=message_text.strip()
		message_text=message_text.replace(".","");
		print message_text
		print number
		fields=string.split(message_text,",")
		print fields
		print len(fields)
		if len(fields) > 1:
#			number=fields[0]
			message_text=fields[1].strip()
		else:
			message_text=fields[0].replace("1","")
			message_text=message_text.replace("2","");
			message_text=message_text.replace("3","");
			message_text=message_text.replace("4","");
			message_text=message_text.replace("5","");
			message_text=message_text.replace("6","");
			message_text=message_text.replace("7","");
			message_text=message_text.replace("8","");
			message_text=message_text.replace("9","");
			message_text=message_text.replace("0","");
			message_text=message_text.replace("(","");
			message_text=message_text.replace(")","");
			message_text=message_text.replace("-","");
			message_text=message_text.strip()
			if(number is None):
				number=voice.voicemail().messages[0].phoneNumber       

		if(number is not None):
			number=number.replace("-","")
			number=number.replace("+","")
			number=number.replace("(","")
			number=number.replace(")","")
			number=number.replace(" ","")


	if newMessage==True:
		#check to see if we have a properly transcribed message
		#this will screw up if we ever request a song with 'transcript' in the name
		if re.match("Transcript", message_text) == None:
			print message_text
			print number
			if voiceRequest:
				voice.voicemail().messages[0].delete()
			if smsRequest:
		       		voice.sms().messages[0].delete()
       			query="TranscriptionText="+urllib.quote(message_text)+"&Caller="+number;
       			print query
       			search=urllib2.Request("http://www.artiswrong.com/ampache/song_callback.php",query)
       			response=urllib2.urlopen(search)
		if re.match("Transcript not available", message_text):  #the transcript's not available, for whatever reason
			voice.voicemail().messages[0].delete()
			#send a message saying we're sorry, but the search is a dud?
			print "uh-oh!  Transcript not available.  Try again!"

							
	else:
		print "no new song requests"
	time.sleep(80)
