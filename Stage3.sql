create database if not exists  lp;
use lp;

create table if not exists lp_book(
	id integer  NOT NULL AUTO_INCREMENT,
	title varchar(128),
	ukey varchar(32),
	PRIMARY KEY PK_lp_bookz (id),
	INDEX ilp_book (ukey)

);

create table if not exists lp_page(
	id integer  NOT NULL AUTO_INCREMENT,
	book_id integer,
	seq integer,
	height integer,
	width integer,
	status char(1),
	filename varchar(256),
	PRIMARY KEY PK_lp_page (id),
	INDEX ilp_page (book_id)
);

/* Stage 2 */
create table if not exists lp_word(
	id integer  NOT NULL AUTO_INCREMENT,
	word varchar(32),
	PRIMARY KEY PK_lp_word (id),
	INDEX ilp_word (word)
);

create table if not exists lp_page_word(
	id integer  NOT NULL AUTO_INCREMENT,
	page_id integer,
	word_id integer,
	seq integer,
	posleft integer,
	postop integer,
	posright integer,
	posbottom integer,
	PRIMARY KEY PK_lp_page_word (id),
	INDEX ilp_page_word_page (page_id),
	INDEX ilp_page_word_word (word_id)
);

-- Stage 3
create table if not exists lp_user(
	id integer  NOT NULL AUTO_INCREMENT,
	email varchar(254),
	password varchar( 32 ),
	connection_key varchar( 32 ),
	PRIMARY KEY PK_lp_used (id)
);

create table if not exists lp_book_del (
	id integer,
	ukey varchar(32),
	PRIMARY KEY PK_lp_book_del (id)
);
