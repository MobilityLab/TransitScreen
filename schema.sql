--
-- PostgreSQL database dump
--

-- Started on 2012-01-06 12:36:11 EST

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 148 (class 1259 OID 16689)
-- Dependencies: 3
-- Name: agency_stop; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE agency_stop (
    id integer NOT NULL,
    block_id integer,
    agency character varying(25),
    stop_id character varying(15)
);


ALTER TABLE public.agency_stop OWNER TO postgres;

--
-- TOC entry 147 (class 1259 OID 16687)
-- Dependencies: 148 3
-- Name: agency_stop_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE agency_stop_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.agency_stop_id_seq OWNER TO postgres;

--
-- TOC entry 1819 (class 0 OID 0)
-- Dependencies: 147
-- Name: agency_stop_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE agency_stop_id_seq OWNED BY agency_stop.id;


--
-- TOC entry 146 (class 1259 OID 16672)
-- Dependencies: 1797 3
-- Name: blocks; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE blocks (
    id integer NOT NULL,
    screen_id integer NOT NULL,
    stop character varying(255),
    custom_name character varying(255),
    custom_body text,
    "column" integer,
    "position" integer DEFAULT 0
);


ALTER TABLE public.blocks OWNER TO postgres;

--
-- TOC entry 145 (class 1259 OID 16670)
-- Dependencies: 146 3
-- Name: blocks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE blocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.blocks_id_seq OWNER TO postgres;

--
-- TOC entry 1820 (class 0 OID 0)
-- Dependencies: 145
-- Name: blocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE blocks_id_seq OWNED BY blocks.id;


--
-- TOC entry 144 (class 1259 OID 16658)
-- Dependencies: 1793 1794 1795 3
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
-- TOC entry 143 (class 1259 OID 16395)
-- Dependencies: 3
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
    screen_version integer
);


ALTER TABLE public.screens OWNER TO postgres;

--
-- TOC entry 1821 (class 0 OID 0)
-- Dependencies: 143
-- Name: COLUMN screens.user_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN screens.user_id IS 'This should correspond to a user.id record value somewhere.';


--
-- TOC entry 142 (class 1259 OID 16393)
-- Dependencies: 143 3
-- Name: screens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE screens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.screens_id_seq OWNER TO postgres;

--
-- TOC entry 1822 (class 0 OID 0)
-- Dependencies: 142
-- Name: screens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE screens_id_seq OWNED BY screens.id;


--
-- TOC entry 141 (class 1259 OID 16387)
-- Dependencies: 1791 3
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
-- TOC entry 140 (class 1259 OID 16385)
-- Dependencies: 3 141
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 1823 (class 0 OID 0)
-- Dependencies: 140
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- TOC entry 1798 (class 2604 OID 16692)
-- Dependencies: 148 147 148
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE agency_stop ALTER COLUMN id SET DEFAULT nextval('agency_stop_id_seq'::regclass);


--
-- TOC entry 1796 (class 2604 OID 16675)
-- Dependencies: 146 145 146
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE blocks ALTER COLUMN id SET DEFAULT nextval('blocks_id_seq'::regclass);


--
-- TOC entry 1792 (class 2604 OID 16398)
-- Dependencies: 142 143 143
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE screens ALTER COLUMN id SET DEFAULT nextval('screens_id_seq'::regclass);


--
-- TOC entry 1790 (class 2604 OID 16390)
-- Dependencies: 140 141 141
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- TOC entry 1810 (class 2606 OID 16695)
-- Dependencies: 148 148
-- Name: agency_stop_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY agency_stop
    ADD CONSTRAINT agency_stop_pkey PRIMARY KEY (id);


--
-- TOC entry 1807 (class 2606 OID 16686)
-- Dependencies: 146 146
-- Name: blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY blocks
    ADD CONSTRAINT blocks_pkey PRIMARY KEY (id);


--
-- TOC entry 1804 (class 2606 OID 16668)
-- Dependencies: 144 144
-- Name: ci_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY ci_sessions
    ADD CONSTRAINT ci_sessions_pkey PRIMARY KEY (session_id);


--
-- TOC entry 1802 (class 2606 OID 16400)
-- Dependencies: 143 143
-- Name: screens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY screens
    ADD CONSTRAINT screens_pkey PRIMARY KEY (id);


--
-- TOC entry 1800 (class 2606 OID 16392)
-- Dependencies: 141 141
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 1811 (class 1259 OID 16701)
-- Dependencies: 148
-- Name: fki_blocks_agency_stop; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_blocks_agency_stop ON agency_stop USING btree (block_id);


--
-- TOC entry 1808 (class 1259 OID 16681)
-- Dependencies: 146
-- Name: fki_screens_blocks; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_screens_blocks ON blocks USING btree (screen_id);


--
-- TOC entry 1805 (class 1259 OID 16669)
-- Dependencies: 144
-- Name: last_activity_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX last_activity_idx ON ci_sessions USING btree (last_activity);


--
-- TOC entry 1813 (class 2606 OID 16696)
-- Dependencies: 1806 146 148
-- Name: blocks_agency_stop; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY agency_stop
    ADD CONSTRAINT blocks_agency_stop FOREIGN KEY (block_id) REFERENCES blocks(id);


--
-- TOC entry 1812 (class 2606 OID 16676)
-- Dependencies: 146 1801 143
-- Name: screens_blocks; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY blocks
    ADD CONSTRAINT screens_blocks FOREIGN KEY (screen_id) REFERENCES screens(id);


--
-- TOC entry 1818 (class 0 OID 0)
-- Dependencies: 3
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2012-01-06 12:36:12 EST

--
-- PostgreSQL database dump complete
--

