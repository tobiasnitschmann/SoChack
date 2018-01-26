import serial
import MySQLdb
import re
import time
import string
import io

db = MySQLdb.connect(host="<DB HOST>",    # your host, usually localhost
                     user="<DB USER>",         # your username
                     passwd="<DB PASSWORT>",  # your password
                     db="<DB NAME>")        # name of the data base

cur = db.cursor()

ser = serial.Serial("/dev/rfcomm0", timeout=None)
ser.baudrate = 9600
ser.flushInput()
ser.write(b'2105\r\n')
ser.flush()
seq = []
while True:
    reading = ser.read()
    seq.append(reading)
    joineddata = ' '.join(str(v) for v in seq).replace(' ', '')

    err = re.search('ERROR', joineddata)
    if err:
        break
    m = re.search('4([^;]*)5:', joineddata) #'/4([^;]*)\n5', joineddata)
    if m:
        ser.close()
        test = str(m.group(0))
        x = (test[-8:])      
        SoC = (int( x[3:5], 16)/2)
        if SoC > 0 & SoC <= 100:
            print(SoC)
            cur.execute("""INSERT INTO bms (soc) VALUES (%s)""", (SoC,) )
            db.commit()
        break