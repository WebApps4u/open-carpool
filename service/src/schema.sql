CREATE TABLE users
(
  id SERIAL NOT NULL,
  name VARCHAR,
  cell_phone_nr VARCHAR(50) NOT NULL,
  is_active BOOL,
  PRIMARY KEY (id)
);

CREATE INDEX cell_phone_nr_idx ON users(cell_phone_nr);


CREATE TYPE enum_route_status AS ENUM ('enabled', 'disabled');
CREATE TABLE routes
(
	id INTEGER NOT NULL,
	origin VARCHAR,
	destination VARCHAR,
    status enum_route_status NOT NULL DEFAULT 'disabled',
	PRIMARY KEY (id)
);

CREATE TYPE enum_ride_status AS ENUM ('open', 'closed', 'updated', 'expired');
--open: is rady for matching
--closed: closed by user
--expired: closed by time
--updated: same as closed, for statistics


CREATE TABLE ride_requests
(
	id SERIAL NOT NULL,
	user_id  INTEGER REFERENCES users(id) NOT NULL,
	route_id INTEGER REFERENCES routes(id) NOT NULL,
	earliest_start_time TIMESTAMP WITH TIME ZONE NOT NULL,
	latest_start_time TIMESTAMP WITH TIME ZONE NOT NULL,
	request_time TIMESTAMP WITH TIME ZONE DEFAULT now(),
	status enum_ride_status NOT NULL,
	PRIMARY KEY (id)
);


CREATE TABLE ride_offers
(
	id SERIAL NOT NULL,
	user_id  INTEGER REFERENCES users(id) NOT NULL,
	route_id INTEGER REFERENCES routes(id) NOT NULL,
	start_time TIMESTAMP WITH TIME ZONE NOT NULL,
	request_time TIMESTAMP WITH TIME ZONE DEFAULT now(),
	status enum_ride_status NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE public_transport
(
    id SERIAL NOT NULL,
    route_id INTEGER REFERENCES routes(id) NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    means TEXT,
    PRIMARY KEY (id)
);

CREATE TABLE taxi
(
    id SERIAL NOT NULL,
    route_id INTEGER REFERENCES routes(id) NOT NULL,
    number VARCHAR(50),
    PRIMARY KEY (id)
);


--table for saving status changes, no ref int because its only for logging
CREATE TABLE status_log
(
    id SERIAL NOT NULL,
    ride_offer_id INTEGER, -- REFERENCES ride_offers(id),
    ride_requests_id INTEGER, -- REFERENCES ride_requests(id),
    old_status enum_ride_status,
    new_status enum_ride_status,
    change_time TIMESTAMP WITH TIME ZONE DEFAULT now(),
    PRIMARY KEY (id)
);


CREATE TABLE sms
(
    id SERIAL NOT NULL,
    message TEXT,
    message_id VARCHAR(20),
    receiver_nr VARCHAR(30),
    cost NUMERIC,
    send_time TIMESTAMP WITH TIME ZONE DEFAULT now(),
    error TEXT DEFAULT NULL,  --in case of a failure the message should go here, in case of succsess this should be NULL
    PRIMARY KEY (id)
);


--PILOT ROUTES
INSERT INTO routes(id, origin, destination) VALUES(41, 'Berlin Wannsee', 'Dreilinden');
INSERT INTO routes(id, origin, destination) VALUES(42, 'Potsdam Main Station', 'Dreilinden');
INSERT INTO routes(id, origin, destination) VALUES(43, 'Berlin Charlottenburg', 'Dreilinden');
INSERT INTO routes(id, origin, destination) VALUES(44, 'Potsdam Babelsberg', 'Dreilinden');
INSERT INTO routes(id, origin, destination) VALUES(45, 'Tegel Airport', 'Dreilinden');
INSERT INTO routes(id, origin, destination) VALUES(46, 'Schoenefeld Airport', 'Dreilinden');

--TEST USER
INSERT INTO users(name, cell_phone_nr) VALUES('Max Mustermann', '+491234')
