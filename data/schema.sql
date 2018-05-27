CREATE TABLE account 
(
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    [owner] VARCHAR(50) NOT NULL, 
    displayName VARCHAR(50) NOT NULL,
    balance DECIMAL(10,2) NOT NULL,
    createdDate DATETIME NOT NULL,
    updatedDate DATETIME NOT NULL,
    isDeleted BOOLEAN NOT NULL
);

CREATE TABLE [transaction]
(
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    accountId INTEGER NOT NULL, 
    amount DECIMAL(10,2) NOT NULL,
    [type] INTEGER NOT NULL,
    balance DECIMAL(10, 2) NOT NULL,
    transferToAccountId INTEGER,
    transferFromAccountId INTEGER,
    [description] VARCHAR(100) NOT NULL,
    createdDate DATETIME NOT NULL
);