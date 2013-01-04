--
-- PostgreSQL database dump
--

-- Dumped from database version 9.0.4
-- Dumped by pg_dump version 9.2.0
-- Started on 2012-11-09 03:05:27 EST

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 471 (class 2612 OID 11574)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO postgres;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 142 (class 1259 OID 34959)
-- Name: agency_stop; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE agency_stop (
    id integer NOT NULL,
    block_id integer,
    agency character varying(25),
    stop_id character varying(25),
    exclusions character varying(100)
);


ALTER TABLE public.agency_stop OWNER TO postgres;

--
-- TOC entry 143 (class 1259 OID 34962)
-- Name: agency_stop_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE agency_stop_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.agency_stop_id_seq OWNER TO postgres;

--
-- TOC entry 1837 (class 0 OID 0)
-- Dependencies: 143
-- Name: agency_stop_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE agency_stop_id_seq OWNED BY agency_stop.id;


--
-- TOC entry 144 (class 1259 OID 34964)
-- Name: blocks; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE blocks (
    id integer NOT NULL,
    screen_id integer NOT NULL,
    stop character varying(255),
    custom_name character varying(255),
    "column" integer,
    "position" integer DEFAULT 0,
    custom_body text,
    "limit" integer
);


ALTER TABLE public.blocks OWNER TO postgres;

--
-- TOC entry 145 (class 1259 OID 34971)
-- Name: blocks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE blocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blocks_id_seq OWNER TO postgres;

--
-- TOC entry 1838 (class 0 OID 0)
-- Dependencies: 145
-- Name: blocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE blocks_id_seq OWNED BY blocks.id;


--
-- TOC entry 146 (class 1259 OID 34973)
-- Name: ci_sessions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE ci_sessions (
    session_id character varying(40) DEFAULT '0'::character varying NOT NULL,
    ip_address character varying(16) DEFAULT '0'::character varying NOT NULL,
    user_agent character varying(120) NOT NULL,
    last_activity integer DEFAULT 0 NOT NULL,
    user_data text NOT NULL
);


ALTER TABLE public.ci_sessions OWNER TO postgres;

--
-- TOC entry 147 (class 1259 OID 34982)
-- Name: screens; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE screens (
    id integer NOT NULL,
    nice_id character(20),
    user_id integer,
    "MoTh_op" time without time zone,
    "MoTh_cl" time without time zone,
    "Fr_op" time without time zone,
    "Fr_cl" time without time zone,
    "Sa_op" time without time zone,
    "Sa_cl" time without time zone,
    "Su_op" time without time zone,
    "Su_cl" time without time zone,
    name character(250),
    screen_version integer,
    zoom numeric DEFAULT 1,
    last_checkin timestamp without time zone,
    lat numeric,
    lon numeric,
    wmata_key text
);


ALTER TABLE public.screens OWNER TO postgres;

--
-- TOC entry 1839 (class 0 OID 0)
-- Dependencies: 147
-- Name: COLUMN screens.user_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN screens.user_id IS 'This should correspond to a user.id record value somewhere.';


--
-- TOC entry 148 (class 1259 OID 34989)
-- Name: screens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE screens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.screens_id_seq OWNER TO postgres;

--
-- TOC entry 1840 (class 0 OID 0)
-- Dependencies: 148
-- Name: screens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE screens_id_seq OWNED BY screens.id;


--
-- TOC entry 149 (class 1259 OID 34991)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    email character(120) NOT NULL,
    password character varying(255) NOT NULL,
    admin boolean DEFAULT false NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 150 (class 1259 OID 34995)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 1841 (class 0 OID 0)
-- Dependencies: 150
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- TOC entry 1805 (class 2604 OID 34997)
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY agency_stop ALTER COLUMN id SET DEFAULT nextval('agency_stop_id_seq'::regclass);


--
-- TOC entry 1807 (class 2604 OID 34998)
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY blocks ALTER COLUMN id SET DEFAULT nextval('blocks_id_seq'::regclass);


--
-- TOC entry 1812 (class 2604 OID 34999)
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY screens ALTER COLUMN id SET DEFAULT nextval('screens_id_seq'::regclass);


--
-- TOC entry 1814 (class 2604 OID 35000)
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- TOC entry 1816 (class 2606 OID 35002)
-- Name: agency_stop_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY agency_stop
    ADD CONSTRAINT agency_stop_pkey PRIMARY KEY (id);


--
-- TOC entry 1819 (class 2606 OID 35004)
-- Name: blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY blocks
    ADD CONSTRAINT blocks_pkey PRIMARY KEY (id);


--
-- TOC entry 1822 (class 2606 OID 35006)
-- Name: ci_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY ci_sessions
    ADD CONSTRAINT ci_sessions_pkey PRIMARY KEY (session_id);


--
-- TOC entry 1825 (class 2606 OID 35008)
-- Name: screens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY screens
    ADD CONSTRAINT screens_pkey PRIMARY KEY (id);


--
-- TOC entry 1827 (class 2606 OID 35010)
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 1817 (class 1259 OID 35011)
-- Name: fki_blocks_agency_stop; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_blocks_agency_stop ON agency_stop USING btree (block_id);


--
-- TOC entry 1820 (class 1259 OID 35012)
-- Name: fki_screens_blocks; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_screens_blocks ON blocks USING btree (screen_id);


--
-- TOC entry 1823 (class 1259 OID 35013)
-- Name: last_activity_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX last_activity_idx ON ci_sessions USING btree (last_activity);


--
-- TOC entry 1828 (class 2606 OID 35014)
-- Name: blocks_agency_stop; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY agency_stop
    ADD CONSTRAINT blocks_agency_stop FOREIGN KEY (block_id) REFERENCES blocks(id);


--
-- TOC entry 1829 (class 2606 OID 42986)
-- Name: screens_blocks; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY blocks
    ADD CONSTRAINT screens_blocks FOREIGN KEY (screen_id) REFERENCES screens(id) ON UPDATE CASCADE;


--
-- TOC entry 1836 (class 0 OID 0)
-- Dependencies: 6
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2012-11-09 03:05:27 EST

--
-- PostgreSQL database dump complete
--

