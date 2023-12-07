--
-- PostgreSQL database dump
--

-- Dumped from database version 15.4 (Ubuntu 15.4-2.pgdg22.04+1)
-- Dumped by pg_dump version 15.5 (Debian 15.5-0+deb12u1)

-- Started on 2023-12-07 18:32:48 EST

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 15 (class 3079 OID 17160)
-- Name: btree_gin; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gin WITH SCHEMA public;


--
-- TOC entry 4566 (class 0 OID 0)
-- Dependencies: 15
-- Name: EXTENSION btree_gin; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION btree_gin IS 'support for indexing common datatypes in GIN';


--
-- TOC entry 19 (class 3079 OID 17703)
-- Name: btree_gist; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;


--
-- TOC entry 4567 (class 0 OID 0)
-- Dependencies: 19
-- Name: EXTENSION btree_gist; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION btree_gist IS 'support for indexing common datatypes in GiST';


--
-- TOC entry 8 (class 3079 OID 16671)
-- Name: citext; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- TOC entry 4568 (class 0 OID 0)
-- Dependencies: 8
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- TOC entry 17 (class 3079 OID 17598)
-- Name: cube; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS cube WITH SCHEMA public;


--
-- TOC entry 4569 (class 0 OID 0)
-- Dependencies: 17
-- Name: EXTENSION cube; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION cube IS 'data type for multidimensional cubes';


--
-- TOC entry 2 (class 3079 OID 16384)
-- Name: dblink; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS dblink WITH SCHEMA public;


--
-- TOC entry 4570 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION dblink; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION dblink IS 'connect to other PostgreSQL databases from within a database';


--
-- TOC entry 14 (class 3079 OID 17155)
-- Name: dict_int; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS dict_int WITH SCHEMA public;


--
-- TOC entry 4571 (class 0 OID 0)
-- Dependencies: 14
-- Name: EXTENSION dict_int; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION dict_int IS 'text search dictionary template for integers';


--
-- TOC entry 20 (class 3079 OID 18353)
-- Name: dict_xsyn; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS dict_xsyn WITH SCHEMA public;


--
-- TOC entry 4572 (class 0 OID 0)
-- Dependencies: 20
-- Name: EXTENSION dict_xsyn; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION dict_xsyn IS 'text search dictionary template for extended synonym processing';


--
-- TOC entry 18 (class 3079 OID 17687)
-- Name: earthdistance; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS earthdistance WITH SCHEMA public;


--
-- TOC entry 4573 (class 0 OID 0)
-- Dependencies: 18
-- Name: EXTENSION earthdistance; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION earthdistance IS 'calculate great-circle distances on the surface of the Earth';


--
-- TOC entry 7 (class 3079 OID 16660)
-- Name: fuzzystrmatch; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS fuzzystrmatch WITH SCHEMA public;


--
-- TOC entry 4574 (class 0 OID 0)
-- Dependencies: 7
-- Name: EXTENSION fuzzystrmatch; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION fuzzystrmatch IS 'determine similarities and distance between strings';


--
-- TOC entry 13 (class 3079 OID 17027)
-- Name: hstore; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;


--
-- TOC entry 4575 (class 0 OID 0)
-- Dependencies: 13
-- Name: EXTENSION hstore; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION hstore IS 'data type for storing sets of (key, value) pairs';


--
-- TOC entry 12 (class 3079 OID 16905)
-- Name: intarray; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS intarray WITH SCHEMA public;


--
-- TOC entry 4576 (class 0 OID 0)
-- Dependencies: 12
-- Name: EXTENSION intarray; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION intarray IS 'functions, operators, and index support for 1-D arrays of integers';


--
-- TOC entry 4 (class 3079 OID 16444)
-- Name: ltree; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS ltree WITH SCHEMA public;


--
-- TOC entry 4577 (class 0 OID 0)
-- Dependencies: 4
-- Name: EXTENSION ltree; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION ltree IS 'data type for hierarchical tree-like structures';


--
-- TOC entry 22 (class 3079 OID 18365)
-- Name: pg_stat_statements; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_stat_statements WITH SCHEMA public;


--
-- TOC entry 4578 (class 0 OID 0)
-- Dependencies: 22
-- Name: EXTENSION pg_stat_statements; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_stat_statements IS 'track planning and execution statistics of all SQL statements executed';


--
-- TOC entry 11 (class 3079 OID 16824)
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- TOC entry 4579 (class 0 OID 0)
-- Dependencies: 11
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- TOC entry 10 (class 3079 OID 16787)
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- TOC entry 4580 (class 0 OID 0)
-- Dependencies: 10
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- TOC entry 16 (class 3079 OID 17596)
-- Name: pgrowlocks; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgrowlocks WITH SCHEMA public;


--
-- TOC entry 4581 (class 0 OID 0)
-- Dependencies: 16
-- Name: EXTENSION pgrowlocks; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgrowlocks IS 'show row-level locking information';


--
-- TOC entry 5 (class 3079 OID 16629)
-- Name: pgstattuple; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgstattuple WITH SCHEMA public;


--
-- TOC entry 4582 (class 0 OID 0)
-- Dependencies: 5
-- Name: EXTENSION pgstattuple; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgstattuple IS 'show tuple-level statistics';


--
-- TOC entry 6 (class 3079 OID 16639)
-- Name: tablefunc; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS tablefunc WITH SCHEMA public;


--
-- TOC entry 4583 (class 0 OID 0)
-- Dependencies: 6
-- Name: EXTENSION tablefunc; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION tablefunc IS 'functions that manipulate whole tables, including crosstab';


--
-- TOC entry 21 (class 3079 OID 18358)
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- TOC entry 4584 (class 0 OID 0)
-- Dependencies: 21
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


--
-- TOC entry 9 (class 3079 OID 16776)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 4585 (class 0 OID 0)
-- Dependencies: 9
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 3 (class 3079 OID 16430)
-- Name: xml2; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS xml2 WITH SCHEMA public;


--
-- TOC entry 4586 (class 0 OID 0)
-- Dependencies: 3
-- Name: EXTENSION xml2; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION xml2 IS 'XPath querying and XSLT';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 246 (class 1259 OID 2258668)
-- Name: character; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public."character" (
    id integer NOT NULL,
    name character varying(255),
    description text,
    image character varying(255)
);


ALTER TABLE public."character" OWNER TO dlifpmcu;

--
-- TOC entry 255 (class 1259 OID 2258744)
-- Name: character_chat; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.character_chat (
    "characterId" integer NOT NULL,
    "chatId" integer NOT NULL
);


ALTER TABLE public.character_chat OWNER TO dlifpmcu;

--
-- TOC entry 245 (class 1259 OID 2258667)
-- Name: character_id_seq; Type: SEQUENCE; Schema: public; Owner: dlifpmcu
--

CREATE SEQUENCE public.character_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.character_id_seq OWNER TO dlifpmcu;

--
-- TOC entry 4587 (class 0 OID 0)
-- Dependencies: 245
-- Name: character_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dlifpmcu
--

ALTER SEQUENCE public.character_id_seq OWNED BY public."character".id;


--
-- TOC entry 250 (class 1259 OID 2258686)
-- Name: chat; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.chat (
    id integer NOT NULL
);


ALTER TABLE public.chat OWNER TO dlifpmcu;

--
-- TOC entry 249 (class 1259 OID 2258685)
-- Name: chat_id_seq; Type: SEQUENCE; Schema: public; Owner: dlifpmcu
--

CREATE SEQUENCE public.chat_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.chat_id_seq OWNER TO dlifpmcu;

--
-- TOC entry 4588 (class 0 OID 0)
-- Dependencies: 249
-- Name: chat_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dlifpmcu
--

ALTER SEQUENCE public.chat_id_seq OWNED BY public.chat.id;


--
-- TOC entry 254 (class 1259 OID 2258731)
-- Name: chat_message; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.chat_message (
    "chatId" integer,
    "messageId" integer
);


ALTER TABLE public.chat_message OWNER TO dlifpmcu;

--
-- TOC entry 257 (class 1259 OID 2258758)
-- Name: image_references; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.image_references (
    id integer NOT NULL,
    image_file_name character varying(255),
    entity_id integer,
    entity_type character varying(255)
);


ALTER TABLE public.image_references OWNER TO dlifpmcu;

--
-- TOC entry 256 (class 1259 OID 2258757)
-- Name: image_references_id_seq; Type: SEQUENCE; Schema: public; Owner: dlifpmcu
--

CREATE SEQUENCE public.image_references_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.image_references_id_seq OWNER TO dlifpmcu;

--
-- TOC entry 4589 (class 0 OID 0)
-- Dependencies: 256
-- Name: image_references_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dlifpmcu
--

ALTER SEQUENCE public.image_references_id_seq OWNED BY public.image_references.id;


--
-- TOC entry 248 (class 1259 OID 2258677)
-- Name: message; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.message (
    id integer NOT NULL,
    content text,
    createdat timestamp without time zone,
    is_human boolean
);


ALTER TABLE public.message OWNER TO dlifpmcu;

--
-- TOC entry 247 (class 1259 OID 2258676)
-- Name: message_id_seq; Type: SEQUENCE; Schema: public; Owner: dlifpmcu
--

CREATE SEQUENCE public.message_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.message_id_seq OWNER TO dlifpmcu;

--
-- TOC entry 4590 (class 0 OID 0)
-- Dependencies: 247
-- Name: message_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dlifpmcu
--

ALTER SEQUENCE public.message_id_seq OWNED BY public.message.id;


--
-- TOC entry 244 (class 1259 OID 2258659)
-- Name: universe; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.universe (
    id integer NOT NULL,
    name character varying(255),
    description text,
    image character varying(255)
);


ALTER TABLE public.universe OWNER TO dlifpmcu;

--
-- TOC entry 253 (class 1259 OID 2258718)
-- Name: universe_character; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.universe_character (
    "universeId" integer,
    "characterId" integer
);


ALTER TABLE public.universe_character OWNER TO dlifpmcu;

--
-- TOC entry 243 (class 1259 OID 2258658)
-- Name: universe_id_seq; Type: SEQUENCE; Schema: public; Owner: dlifpmcu
--

CREATE SEQUENCE public.universe_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.universe_id_seq OWNER TO dlifpmcu;

--
-- TOC entry 4591 (class 0 OID 0)
-- Dependencies: 243
-- Name: universe_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dlifpmcu
--

ALTER SEQUENCE public.universe_id_seq OWNED BY public.universe.id;


--
-- TOC entry 242 (class 1259 OID 2258646)
-- Name: user; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    "firstName" character varying(255),
    "lastName" character varying(255),
    username character varying(255),
    password character varying(255),
    email character varying(255)
);


ALTER TABLE public."user" OWNER TO dlifpmcu;

--
-- TOC entry 252 (class 1259 OID 2258705)
-- Name: user_chat; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.user_chat (
    "userId" integer,
    "chatId" integer
);


ALTER TABLE public.user_chat OWNER TO dlifpmcu;

--
-- TOC entry 241 (class 1259 OID 2258645)
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: dlifpmcu
--

CREATE SEQUENCE public.user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO dlifpmcu;

--
-- TOC entry 4592 (class 0 OID 0)
-- Dependencies: 241
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dlifpmcu
--

ALTER SEQUENCE public.user_id_seq OWNED BY public."user".id;


--
-- TOC entry 251 (class 1259 OID 2258692)
-- Name: user_universe; Type: TABLE; Schema: public; Owner: dlifpmcu
--

CREATE TABLE public.user_universe (
    "userId" integer,
    "universeId" integer
);


ALTER TABLE public.user_universe OWNER TO dlifpmcu;

--
-- TOC entry 4370 (class 2604 OID 2258671)
-- Name: character id; Type: DEFAULT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public."character" ALTER COLUMN id SET DEFAULT nextval('public.character_id_seq'::regclass);


--
-- TOC entry 4372 (class 2604 OID 2258689)
-- Name: chat id; Type: DEFAULT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.chat ALTER COLUMN id SET DEFAULT nextval('public.chat_id_seq'::regclass);


--
-- TOC entry 4373 (class 2604 OID 2258761)
-- Name: image_references id; Type: DEFAULT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.image_references ALTER COLUMN id SET DEFAULT nextval('public.image_references_id_seq'::regclass);


--
-- TOC entry 4371 (class 2604 OID 2258680)
-- Name: message id; Type: DEFAULT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.message ALTER COLUMN id SET DEFAULT nextval('public.message_id_seq'::regclass);


--
-- TOC entry 4369 (class 2604 OID 2258662)
-- Name: universe id; Type: DEFAULT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.universe ALTER COLUMN id SET DEFAULT nextval('public.universe_id_seq'::regclass);


--
-- TOC entry 4368 (class 2604 OID 2258649)
-- Name: user id; Type: DEFAULT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public."user" ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);


--
-- TOC entry 4549 (class 0 OID 2258668)
-- Dependencies: 246
-- Data for Name: character; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public."character" (id, name, description, image) FROM stdin;
\.


--
-- TOC entry 4558 (class 0 OID 2258744)
-- Dependencies: 255
-- Data for Name: character_chat; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.character_chat ("characterId", "chatId") FROM stdin;
\.


--
-- TOC entry 4553 (class 0 OID 2258686)
-- Dependencies: 250
-- Data for Name: chat; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.chat (id) FROM stdin;
\.


--
-- TOC entry 4557 (class 0 OID 2258731)
-- Dependencies: 254
-- Data for Name: chat_message; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.chat_message ("chatId", "messageId") FROM stdin;
\.


--
-- TOC entry 4560 (class 0 OID 2258758)
-- Dependencies: 257
-- Data for Name: image_references; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.image_references (id, image_file_name, entity_id, entity_type) FROM stdin;
\.


--
-- TOC entry 4551 (class 0 OID 2258677)
-- Dependencies: 248
-- Data for Name: message; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.message (id, content, createdat, is_human) FROM stdin;
\.


--
-- TOC entry 4547 (class 0 OID 2258659)
-- Dependencies: 244
-- Data for Name: universe; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.universe (id, name, description, image) FROM stdin;
\.


--
-- TOC entry 4556 (class 0 OID 2258718)
-- Dependencies: 253
-- Data for Name: universe_character; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.universe_character ("universeId", "characterId") FROM stdin;
\.


--
-- TOC entry 4545 (class 0 OID 2258646)
-- Dependencies: 242
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public."user" (id, "firstName", "lastName", username, password, email) FROM stdin;
\.


--
-- TOC entry 4555 (class 0 OID 2258705)
-- Dependencies: 252
-- Data for Name: user_chat; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.user_chat ("userId", "chatId") FROM stdin;
\.


--
-- TOC entry 4554 (class 0 OID 2258692)
-- Dependencies: 251
-- Data for Name: user_universe; Type: TABLE DATA; Schema: public; Owner: dlifpmcu
--

COPY public.user_universe ("userId", "universeId") FROM stdin;
\.


--
-- TOC entry 4593 (class 0 OID 0)
-- Dependencies: 245
-- Name: character_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dlifpmcu
--

SELECT pg_catalog.setval('public.character_id_seq', 1, false);


--
-- TOC entry 4594 (class 0 OID 0)
-- Dependencies: 249
-- Name: chat_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dlifpmcu
--

SELECT pg_catalog.setval('public.chat_id_seq', 1, false);


--
-- TOC entry 4595 (class 0 OID 0)
-- Dependencies: 256
-- Name: image_references_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dlifpmcu
--

SELECT pg_catalog.setval('public.image_references_id_seq', 1, false);


--
-- TOC entry 4596 (class 0 OID 0)
-- Dependencies: 247
-- Name: message_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dlifpmcu
--

SELECT pg_catalog.setval('public.message_id_seq', 1, false);


--
-- TOC entry 4597 (class 0 OID 0)
-- Dependencies: 243
-- Name: universe_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dlifpmcu
--

SELECT pg_catalog.setval('public.universe_id_seq', 1, false);


--
-- TOC entry 4598 (class 0 OID 0)
-- Dependencies: 241
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dlifpmcu
--

SELECT pg_catalog.setval('public.user_id_seq', 1, false);


--
-- TOC entry 4383 (class 2606 OID 2258675)
-- Name: character character_pkey; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public."character"
    ADD CONSTRAINT character_pkey PRIMARY KEY (id);


--
-- TOC entry 4387 (class 2606 OID 2258691)
-- Name: chat chat_pkey; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.chat
    ADD CONSTRAINT chat_pkey PRIMARY KEY (id);


--
-- TOC entry 4389 (class 2606 OID 2258765)
-- Name: image_references image_references_pkey; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.image_references
    ADD CONSTRAINT image_references_pkey PRIMARY KEY (id);


--
-- TOC entry 4385 (class 2606 OID 2258684)
-- Name: message message_pkey; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.message
    ADD CONSTRAINT message_pkey PRIMARY KEY (id);


--
-- TOC entry 4381 (class 2606 OID 2258666)
-- Name: universe universe_pkey; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.universe
    ADD CONSTRAINT universe_pkey PRIMARY KEY (id);


--
-- TOC entry 4375 (class 2606 OID 2258657)
-- Name: user user_email_key; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_email_key UNIQUE (email);


--
-- TOC entry 4377 (class 2606 OID 2258653)
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- TOC entry 4379 (class 2606 OID 2258655)
-- Name: user user_username_key; Type: CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_username_key UNIQUE (username);


--
-- TOC entry 4398 (class 2606 OID 2258747)
-- Name: character_chat character_chat_characterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.character_chat
    ADD CONSTRAINT "character_chat_characterId_fkey" FOREIGN KEY ("characterId") REFERENCES public."character"(id);


--
-- TOC entry 4399 (class 2606 OID 2258752)
-- Name: character_chat character_chat_chatId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.character_chat
    ADD CONSTRAINT "character_chat_chatId_fkey" FOREIGN KEY ("chatId") REFERENCES public.chat(id);


--
-- TOC entry 4396 (class 2606 OID 2258734)
-- Name: chat_message chat_message_chatId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.chat_message
    ADD CONSTRAINT "chat_message_chatId_fkey" FOREIGN KEY ("chatId") REFERENCES public.chat(id);


--
-- TOC entry 4397 (class 2606 OID 2258739)
-- Name: chat_message chat_message_messageId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.chat_message
    ADD CONSTRAINT "chat_message_messageId_fkey" FOREIGN KEY ("messageId") REFERENCES public.message(id);


--
-- TOC entry 4394 (class 2606 OID 2258726)
-- Name: universe_character universe_character_characterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.universe_character
    ADD CONSTRAINT "universe_character_characterId_fkey" FOREIGN KEY ("characterId") REFERENCES public."character"(id);


--
-- TOC entry 4395 (class 2606 OID 2258721)
-- Name: universe_character universe_character_universeId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.universe_character
    ADD CONSTRAINT "universe_character_universeId_fkey" FOREIGN KEY ("universeId") REFERENCES public.universe(id);


--
-- TOC entry 4392 (class 2606 OID 2258713)
-- Name: user_chat user_chat_chatId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.user_chat
    ADD CONSTRAINT "user_chat_chatId_fkey" FOREIGN KEY ("chatId") REFERENCES public.chat(id);


--
-- TOC entry 4393 (class 2606 OID 2258708)
-- Name: user_chat user_chat_userId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.user_chat
    ADD CONSTRAINT "user_chat_userId_fkey" FOREIGN KEY ("userId") REFERENCES public."user"(id);


--
-- TOC entry 4390 (class 2606 OID 2258700)
-- Name: user_universe user_universe_universeId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.user_universe
    ADD CONSTRAINT "user_universe_universeId_fkey" FOREIGN KEY ("universeId") REFERENCES public.universe(id);


--
-- TOC entry 4391 (class 2606 OID 2258695)
-- Name: user_universe user_universe_userId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: dlifpmcu
--

ALTER TABLE ONLY public.user_universe
    ADD CONSTRAINT "user_universe_userId_fkey" FOREIGN KEY ("userId") REFERENCES public."user"(id);


-- Completed on 2023-12-07 18:32:52 EST

--
-- PostgreSQL database dump complete
--

