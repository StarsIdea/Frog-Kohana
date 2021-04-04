Importer
========

The importer is built to the specification of the "master ride results" spreadsheet. Some extra data is added programatically, which is described below:

### **Ride**
- Date: C4
- Name: C5
- Location: C6
- Manager: C7
- Secretary: C8
- Veterinarian: C9
- Distance: C10

The ride name is also prepended with the year programatically.

### **Event**
The event name is composed of the sheet title + " miles" (ie. 50 miles)

### **Event Results**
- BC Horse Sr: J7
- BC Score Sr: J8
- BC Horse Jr: O7
- BC Score Jr: O8

Senior results start on the line following "Placing".

Senior DNF start 2 or more lines after the senior placings, on the same line as "DNF" in column A.

Junior results follow the Senior DNF, one line following "Juniors" in column A.

Junior DNF start 2 or more lines after the junior placings, on the same line as "DNF" in column A.


Troubleshooting
---------------
### **The event result times are not correct at all.**

Make sure the time column values are formatted in excel as follows (after going to Format > Cells):

- Number
- Category: Time
- Type: 13:30:55


