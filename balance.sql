PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE "owners" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" TEXT NOT NULL
);

CREATE TABLE "trans_to_category" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "cat_id" INTEGER NOT NULL,
    "trans_id" INTEGER NOT NULL,
    "comment" TEXT
);

CREATE TABLE transactions (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "date" DATETIME NOT NULL,
    "reference_id" TEXT NOT NULL,
    "reference_desc" TEXT NOT NULL,
    "full_amount" REAL NOT NULL,
    "current_amount" REAL NOT NULL,
    "owner" INTEGER NOT NULL,
    "account" INTEGER NOT NULL
);

CREATE TABLE "accounts" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" TEXT NOT NULL
);
CREATE TABLE categories (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" TEXT NOT NULL
);

CREATE TABLE "words_to_category" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "cat_id" INTEGER NOT NULL,
    "word" TEXT NOT NULL UNIQUE
);

CREATE VIEW "complete" AS SELECT transactions.date, transactions.reference_id, transactions.reference_desc, transactions.current_amount, owners.name AS owner_name, accounts.name AS account_name 
FROM transactions 
JOIN owners ON owners.id = transactions.owner 
JOIN accounts ON accounts.id = transactions.account;

COMMIT;
