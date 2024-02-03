# Inventory

**An inventory app for [Nextcloud](http://nextcloud.com). Manage your inventory.**

![inventory](https://raw.githubusercontent.com/raimund-schluessler/inventory/master/screenshots/inventory-1.png)

## Features

- List all items and their properties in your inventory
- Sort items into folders or places
- Link parent, related and sub items
- Upload attachments such as invoices and manuals
- Upload images for items
- Find item instances by their storage place
- Assign unique UUIDs and find item instances by scanning their GTIN or QR code
- Assign tags to items and search by tag

## Build the app

To build you need to have [Node.js](https://nodejs.org/en/) installed.

- Install JS dependencies: `npm ci`
- Build JavaScript for the frontend
    - `npm run dev` development build
    - `npm run watch` watch for changes
    - `npm run build` production build 

Read more about [necessary prerequisites](https://docs.nextcloud.com/server/latest/admin_manual/installation/source_installation.html#prerequisites-for-manual-installation) for manual installs.

## Running tests

You can run the front-end tests by using:

```
npm run test
```
