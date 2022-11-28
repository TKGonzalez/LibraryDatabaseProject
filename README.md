# libraryDB
[Fall 2022 - COSC 3380] Database Systems Group Project - LibraryDB

Requirements of this document

ReadMe file to describe and give detailed instructions about the files you are submitting as well as any specific steps to follow for installing your project.  
•Include a project document which details what database application you have built -- (1) types of data that can be added, modified, and edited;  (2) types of user roles in your application; (3) the semantic constraints which are implemented as triggers; and (4) types of queries/reports available in your application.

Document of how to navigate and implement the triggers / reports and queries 

What the project is, what data entry can be done, what you can do, what triggers are there (what is the semantic constraint being enforced) what reports and queries have been set up

***

Project: Library database

Group: 12

Description: This project consists of an online web app that provides a frontend to users (Students, Faculty, Admins) combined with a MySQL backend that powers the database. Users can navigate through different categories of items (Books, DVDs, Laptops, Meeting Rooms) and search through them for keywords. They also have the ability to ‘borrow’ or ‘hold’ items through a simple button click. The number of things and the length they can be borrowed differs for the different user roles - faculty, for instance, have more privileges in that regard than students. If these items are not returned within the specified due date, fines will begin to be applied. Users can register and login after verifying their emails. They also possess the ability to change certain pieces of information in their profile. Under the ‘Trending’ Tab users can see which books have been checked out more than four times in the last fifteen days. On ‘User Reports’ information regarding the user’s balance can be found. The information on this page is drawn from several tables. Accessible on this page is a button allowing the user to see all their transaction history, including the ability to filter by date. Admins have access to a full-featured admin dashboard. From here, they can see statistics on new accounts, total and active accounts, as well as edit and delete the information on all accounts. On ‘Hold Request’ admins can view holds and change their status, or delete them. Admins can also use a button to send reminder emails regarding outstanding holds. On ‘Accounts’ admins can create accounts, view and filter accounts, and edit and delete accounts. The most important functionality on this page is ‘View’, from where admins can view the transaction history of specific users and return their items. They can also manually edit user balances from here. On ‘Inventory’ admins can see the full item catalog and search through it. When filtering by item category, admins also have the ability to create new items. On ‘Reports’ admins have the ability to search through most borrowed books by date. This draws from multiple tables. ‘Roles’ simply shows the number of accounts by user role. 


User Roles: Student, Faculty, Admin
	Student: Limited to 2 items and can borrow items for 3 days before they are late.
	Faculty: Limited to 4 items and can borrow items for 5 days before they are late.
	Admin: Access to Admin Dashboard


How to activate triggers:
Trigger 1: This trigger can be activated in 5 ways and enforces 5 constraints including item limits for user roles.
1) Attempt to reserve 2 meeting rooms. Users are limited to 1 meeting room at a time.
2) Attempt to borrow/hold 2 laptops. Users are limited to 1 laptop at a time.
3) Attempt to borrow/hold an item while the user has late fees due. Users must have no late fees to make a transaction.
4) For the student role, attempt to borrow/hold more than 2 items. Students are limited to 2 items total between books, DVDs, and laptops.
5) For the faculty role, attempt to borrow/hold more than 4 items. Faculty are limited to 4 items total between books, DVDs, and laptops.

Trigger 2: This trigger cannot be activated directly by any users. Instead it is activated by an automatic updater event created in the database. The event updates the “current date” in the database to the next date every day at 6:00pm CST (It updates at 6:00pm so it is easier to work with and see the effects as opposed to midnight). After this update, all users with overdue items will get charged $1 per day per item until the items are returned. The effects of this trigger can be seen on the “user reports” page under the “amount owed” column.
