--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: vehicle_make; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.vehicle_make AS ENUM (
    'Toyota',
    'Nissan',
    'Peugeot',
    'Isuzu',
    'Honda',
    'Ford',
    'Mitsubishi',
    'Volkswagen',
    'Mazda',
    'Subaru',
    'Land Rover'
);


ALTER TYPE public.vehicle_make OWNER TO postgres;

--
-- Name: calculate_distance(public.geometry, public.geometry); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.calculate_distance(point1 public.geometry, point2 public.geometry) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN ST_Distance(
        ST_Transform(point1, 3857),
        ST_Transform(point2, 3857)
    ) / 1000; -- Convert meters to kilometers
END;
$$;


ALTER FUNCTION public.calculate_distance(point1 public.geometry, point2 public.geometry) OWNER TO postgres;

--
-- Name: calculate_trip_route_length(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.calculate_trip_route_length(trip_id integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    start_point GEOMETRY(Point, 4326);
    end_point GEOMETRY(Point, 4326);
BEGIN
    SELECT 
        t.start_location,
        t.end_location
    INTO 
        start_point,
        end_point
    FROM trips t
    WHERE t.id = trip_id;

    IF start_point IS NULL OR end_point IS NULL THEN
        RETURN NULL;
    END IF;

    RETURN calculate_distance(start_point, end_point);
END;
$$;


ALTER FUNCTION public.calculate_trip_route_length(trip_id integer) OWNER TO postgres;

--
-- Name: find_nearby_active_trips(public.geometry, numeric); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.find_nearby_active_trips(center_point public.geometry, radius_km numeric) RETURNS TABLE(trip_id integer, vehicle_id integer, driver_id integer, start_time timestamp without time zone, distance_km numeric, status_name character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        t.id,
        t.vehicle_id,
        t.driver_id,
        t.start_time,
        calculate_distance(center_point, t.start_location) as distance_km,
        ts.name as status_name
    FROM trips t
    JOIN trip_statuses ts ON t.status_id = ts.id
    WHERE 
        ST_DWithin(
            ST_Transform(t.start_location, 3857),
            ST_Transform(center_point, 3857),
            radius_km * 1000
        )
        AND ts.name IN ('In Progress', 'Scheduled')
    ORDER BY distance_km;
END;
$$;


ALTER FUNCTION public.find_nearby_active_trips(center_point public.geometry, radius_km numeric) OWNER TO postgres;

--
-- Name: find_trips_along_route(public.geometry, numeric); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.find_trips_along_route(route_line public.geometry, max_distance_km numeric) RETURNS TABLE(trip_id integer, vehicle_id integer, driver_id integer, start_time timestamp without time zone, distance_to_route numeric)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        t.id,
        t.vehicle_id,
        t.driver_id,
        t.start_time,
        ST_Distance(
            ST_Transform(t.start_location, 3857),
            ST_Transform(route_line, 3857)
        ) / 1000 as distance_to_route
    FROM trips t
    WHERE ST_DWithin(
        ST_Transform(t.start_location, 3857),
        ST_Transform(route_line, 3857),
        max_distance_km * 1000
    )
    ORDER BY distance_to_route;
END;
$$;


ALTER FUNCTION public.find_trips_along_route(route_line public.geometry, max_distance_km numeric) OWNER TO postgres;

--
-- Name: find_trips_in_area(public.geometry); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.find_trips_in_area(area_polygon public.geometry) RETURNS TABLE(trip_id integer, vehicle_id integer, driver_id integer, start_time timestamp without time zone, end_time timestamp without time zone, status_id integer)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        t.id,
        t.vehicle_id,
        t.driver_id,
        t.start_time,
        t.end_time,
        t.status_id
    FROM trips t
    WHERE ST_Within(t.start_location, area_polygon)
    ORDER BY t.start_time DESC;
END;
$$;


ALTER FUNCTION public.find_trips_in_area(area_polygon public.geometry) OWNER TO postgres;

--
-- Name: find_trips_within_radius(public.geometry, numeric); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.find_trips_within_radius(center_point public.geometry, radius_km numeric) RETURNS TABLE(trip_id integer, vehicle_id integer, driver_id integer, start_time timestamp without time zone, distance_km numeric)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        t.id,
        t.vehicle_id,
        t.driver_id,
        t.start_time,
        calculate_distance(center_point, t.start_location) as distance_km
    FROM trips t
    WHERE ST_DWithin(
        ST_Transform(t.start_location, 3857),
        ST_Transform(center_point, 3857),
        radius_km * 1000 -- Convert km to meters
    )
    ORDER BY distance_km;
END;
$$;


ALTER FUNCTION public.find_trips_within_radius(center_point public.geometry, radius_km numeric) OWNER TO postgres;

--
-- Name: generate_media_storage_path(character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.generate_media_storage_path(p_filename character varying, p_media_type character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_date_path VARCHAR;
    v_unique_id VARCHAR;
    v_extension VARCHAR;
BEGIN
    -- Get file extension
    v_extension := LOWER(SPLIT_PART(p_filename, '.', -1));
    
    -- Generate date-based path (YYYY/MM/DD)
    v_date_path := TO_CHAR(CURRENT_DATE, 'YYYY/MM/DD');
    
    -- Generate unique identifier
    v_unique_id := MD5(p_filename || CURRENT_TIMESTAMP::TEXT || RANDOM()::TEXT);
    
    -- Return full path
    RETURN p_media_type || '/' || v_date_path || '/' || v_unique_id || '.' || v_extension;
END;
$$;


ALTER FUNCTION public.generate_media_storage_path(p_filename character varying, p_media_type character varying) OWNER TO postgres;

--
-- Name: get_vehicle_statistics(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_vehicle_statistics() RETURNS TABLE(make public.vehicle_make, model_count bigint, total_vehicles bigint, avg_odometer numeric, maintenance_due_count bigint)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        v.make::vehicle_make,
        COUNT(DISTINCT v.model) as model_count,
        COUNT(*) as total_vehicles,
        AVG(v.current_odometer) as avg_odometer,
        COUNT(*) FILTER (WHERE v.next_maintenance <= CURRENT_DATE) as maintenance_due_count
    FROM vehicles v
    GROUP BY v.make
    ORDER BY total_vehicles DESC;
END;
$$;


ALTER FUNCTION public.get_vehicle_statistics() OWNER TO postgres;

--
-- Name: handle_login_attempt(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.handle_login_attempt() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.login_attempts >= 5 THEN
        NEW.locked_until = CURRENT_TIMESTAMP + INTERVAL '15 minutes';
        NEW.login_attempts = 0;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.handle_login_attempt() OWNER TO postgres;

--
-- Name: handle_media_upload(character varying, character varying, integer, integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.handle_media_upload(p_filename character varying, p_mime_type character varying, p_file_size integer, p_uploaded_by_id integer, p_is_public boolean DEFAULT false) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_media_id INTEGER;
    v_storage_path VARCHAR;
    v_media_type_id INTEGER;
BEGIN
    -- Validate upload
    IF NOT validate_media_upload(p_filename, p_mime_type, p_file_size) THEN
        RAISE EXCEPTION 'Invalid media upload';
    END IF;
    
    -- Get media type ID
    SELECT id INTO v_media_type_id
    FROM media_types
    WHERE mime_type = p_mime_type;
    
    -- Generate storage path
    v_storage_path := generate_media_storage_path(p_filename, SPLIT_PART(p_mime_type, '/', 1));
    
    -- Insert into media_storage
    INSERT INTO media_storage (
        original_filename,
        storage_path,
        file_size_bytes,
        mime_type,
        media_type_id,
        uploaded_by_id,
        is_public
    ) VALUES (
        p_filename,
        v_storage_path,
        p_file_size,
        p_mime_type,
        v_media_type_id,
        p_uploaded_by_id,
        p_is_public
    ) RETURNING id INTO v_media_id;
    
    RETURN v_media_id;
END;
$$;


ALTER FUNCTION public.handle_media_upload(p_filename character varying, p_mime_type character varying, p_file_size integer, p_uploaded_by_id integer, p_is_public boolean) OWNER TO postgres;

--
-- Name: link_media_to_entity(integer, character varying, integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.link_media_to_entity(p_media_id integer, p_related_table character varying, p_related_id integer, p_relation_type character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_relation_id INTEGER;
BEGIN
    INSERT INTO media_relations (
        media_id,
        related_table,
        related_id,
        relation_type
    ) VALUES (
        p_media_id,
        p_related_table,
        p_related_id,
        p_relation_type
    ) RETURNING id INTO v_relation_id;
    
    RETURN v_relation_id;
END;
$$;


ALTER FUNCTION public.link_media_to_entity(p_media_id integer, p_related_table character varying, p_related_id integer, p_relation_type character varying) OWNER TO postgres;

--
-- Name: reset_login_attempts(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.reset_login_attempts() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.last_login IS NOT NULL AND OLD.last_login IS DISTINCT FROM NEW.last_login THEN
        NEW.login_attempts = 0;
        NEW.locked_until = NULL;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.reset_login_attempts() OWNER TO postgres;

--
-- Name: standardize_vehicle_data(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.standardize_vehicle_data() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.make := standardize_vehicle_make(NEW.make)::TEXT;
    NEW.model := standardize_vehicle_model(NEW.model, standardize_vehicle_make(NEW.make));
    -- Clean registration number
    IF NEW.registration_no IS NOT NULL THEN
        NEW.registration_no := clean_registration_number(NEW.registration_no);
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.standardize_vehicle_data() OWNER TO postgres;

--
-- Name: standardize_vehicle_make(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.standardize_vehicle_make(make_name text) RETURNS public.vehicle_make
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN CASE 
        WHEN UPPER(make_name) LIKE '%LAND%CRUISER%' OR 
             UPPER(make_name) LIKE '%L/CRUISER%' OR 
             UPPER(make_name) LIKE '%TOYOTA%' OR
             UPPER(make_name) LIKE '%PRADO%' OR
             UPPER(make_name) LIKE '%COROLLA%' OR
             UPPER(make_name) LIKE '%HILUX%' OR
             UPPER(make_name) LIKE '%FORTUNER%' OR
             UPPER(make_name) LIKE '%RAV4%' OR
             UPPER(make_name) LIKE '%HIACE%' THEN 'Toyota'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%NISSAN%' OR
             UPPER(make_name) LIKE '%X-TRAIL%' OR
             UPPER(make_name) LIKE '%XTRAIL%' OR
             UPPER(make_name) LIKE '%PATROL%' OR
             UPPER(make_name) LIKE '%NAVARA%' OR
             UPPER(make_name) LIKE '%URVAN%' OR
             UPPER(make_name) LIKE '%CIVILIAN%' THEN 'Nissan'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%PEUGEOT%' OR
             UPPER(make_name) LIKE '%3008%' OR
             UPPER(make_name) LIKE '%508%' OR
             UPPER(make_name) LIKE '%504%' THEN 'Peugeot'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%ISUZU%' OR
             UPPER(make_name) LIKE '%D-MAX%' OR
             UPPER(make_name) LIKE '%DMAX%' OR
             UPPER(make_name) LIKE '%MUX%' OR
             UPPER(make_name) LIKE '%FRR%' OR
             UPPER(make_name) LIKE '%NQR%' THEN 'Isuzu'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%HONDA%' OR
             UPPER(make_name) LIKE '%CR-V%' OR
             UPPER(make_name) LIKE '%CRV%' THEN 'Honda'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%FORD%' OR
             UPPER(make_name) LIKE '%EVEREST%' OR
             UPPER(make_name) LIKE '%RANGER%' THEN 'Ford'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%MITSUBISHI%' OR
             UPPER(make_name) LIKE '%PAJERO%' THEN 'Mitsubishi'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%VOLKSWAGEN%' OR 
             UPPER(make_name) LIKE '%VW%' OR
             UPPER(make_name) LIKE '%PASSAT%' OR
             UPPER(make_name) LIKE '%TIGUAN%' THEN 'Volkswagen'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%MAZDA%' OR
             UPPER(make_name) LIKE '%CX-5%' OR
             UPPER(make_name) LIKE '%CX5%' THEN 'Mazda'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%SUBARU%' OR
             UPPER(make_name) LIKE '%OUTBACK%' THEN 'Subaru'::vehicle_make
        
        WHEN UPPER(make_name) LIKE '%LAND%ROVER%' OR
             UPPER(make_name) LIKE '%L/R%' OR
             UPPER(make_name) LIKE '%DISCOVERY%' OR
             UPPER(make_name) LIKE '%FREELANDER%' OR
             UPPER(make_name) LIKE '%DEFENDER%' THEN 'Land Rover'::vehicle_make
        
        ELSE NULL
    END;
END;
$$;


ALTER FUNCTION public.standardize_vehicle_make(make_name text) OWNER TO postgres;

--
-- Name: standardize_vehicle_model(text, public.vehicle_make); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.standardize_vehicle_model(model_name text, make_name public.vehicle_make) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN CASE make_name
        WHEN 'Toyota' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%PRADO%' THEN 'Prado'
                WHEN UPPER(model_name) LIKE '%COROLLA%' THEN 'Corolla'
                WHEN UPPER(model_name) LIKE '%HILUX%' THEN 'Hilux'
                WHEN UPPER(model_name) LIKE '%FORTUNER%' THEN 'Fortuner'
                WHEN UPPER(model_name) LIKE '%RAV4%' THEN 'RAV4'
                WHEN UPPER(model_name) LIKE '%HIACE%' THEN 'Hiace'
                WHEN UPPER(model_name) LIKE '%LAND%CRUISER%' THEN 'Land Cruiser'
                ELSE NULL
            END
        
        WHEN 'Nissan' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%X-TRAIL%' OR UPPER(model_name) LIKE '%XTRAIL%' THEN 'X-Trail'
                WHEN UPPER(model_name) LIKE '%PATROL%' THEN 'Patrol'
                WHEN UPPER(model_name) LIKE '%NAVARA%' THEN 'Navara'
                WHEN UPPER(model_name) LIKE '%URVAN%' THEN 'Urvan'
                WHEN UPPER(model_name) LIKE '%CIVILIAN%' THEN 'Civilian'
                ELSE NULL
            END
        
        WHEN 'Peugeot' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%3008%' THEN '3008'
                WHEN UPPER(model_name) LIKE '%508%' THEN '508'
                WHEN UPPER(model_name) LIKE '%504%' THEN '504'
                ELSE NULL
            END
        
        WHEN 'Isuzu' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%D-MAX%' OR UPPER(model_name) LIKE '%DMAX%' THEN 'D-Max'
                WHEN UPPER(model_name) LIKE '%MUX%' THEN 'Mux'
                WHEN UPPER(model_name) LIKE '%FRR%' THEN 'FRR'
                WHEN UPPER(model_name) LIKE '%NQR%' THEN 'NQR'
                ELSE NULL
            END
        
        WHEN 'Honda' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%CR-V%' OR UPPER(model_name) LIKE '%CRV%' THEN 'CR-V'
                ELSE NULL
            END
        
        WHEN 'Ford' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%EVEREST%' THEN 'Everest'
                WHEN UPPER(model_name) LIKE '%RANGER%' THEN 'Ranger'
                ELSE NULL
            END
        
        WHEN 'Mitsubishi' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%PAJERO%' THEN 'Pajero'
                ELSE NULL
            END
        
        WHEN 'Volkswagen' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%PASSAT%' THEN 'Passat'
                WHEN UPPER(model_name) LIKE '%TIGUAN%' THEN 'Tiguan'
                ELSE NULL
            END
        
        WHEN 'Mazda' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%CX-5%' OR UPPER(model_name) LIKE '%CX5%' THEN 'CX-5'
                ELSE NULL
            END
        
        WHEN 'Subaru' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%OUTBACK%' THEN 'Outback'
                ELSE NULL
            END
        
        WHEN 'Land Rover' THEN
            CASE 
                WHEN UPPER(model_name) LIKE '%DISCOVERY%' THEN 'Discovery'
                WHEN UPPER(model_name) LIKE '%FREELANDER%' THEN 'Freelander'
                WHEN UPPER(model_name) LIKE '%DEFENDER%' THEN 'Defender'
                ELSE NULL
            END
        
        ELSE NULL
    END;
END;
$$;


ALTER FUNCTION public.standardize_vehicle_model(model_name text, make_name public.vehicle_make) OWNER TO postgres;

--
-- Name: track_driver_changes(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.track_driver_changes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        INSERT INTO driver_history (
            driver_id,
            changed_by,
            change_type,
            old_values,
            new_values
        ) VALUES (
            OLD.id,
            NULL, -- TODO: Add user_id when authentication is implemented
            'UPDATE',
            row_to_json(OLD),
            row_to_json(NEW)
        );
    ELSIF TG_OP = 'DELETE' THEN
        INSERT INTO driver_history (
            driver_id,
            changed_by,
            change_type,
            old_values,
            new_values
        ) VALUES (
            OLD.id,
            NULL, -- TODO: Add user_id when authentication is implemented
            'DELETE',
            row_to_json(OLD),
            NULL
        );
    END IF;
    RETURN NULL;
END;
$$;


ALTER FUNCTION public.track_driver_changes() OWNER TO postgres;

--
-- Name: track_office_changes(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.track_office_changes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        INSERT INTO office_history (
            office_id,
            changed_by,
            change_type,
            old_values,
            new_values
        ) VALUES (
            OLD.id,
            NULL, -- TODO: Add user_id when authentication is implemented
            'UPDATE',
            row_to_json(OLD),
            row_to_json(NEW)
        );
    ELSIF TG_OP = 'DELETE' THEN
        INSERT INTO office_history (
            office_id,
            changed_by,
            change_type,
            old_values,
            new_values
        ) VALUES (
            OLD.id,
            NULL, -- TODO: Add user_id when authentication is implemented
            'DELETE',
            row_to_json(OLD),
            NULL
        );
    END IF;
    RETURN NULL;
END;
$$;


ALTER FUNCTION public.track_office_changes() OWNER TO postgres;

--
-- Name: track_user_changes(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.track_user_changes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$ BEGIN IF TG_OP = 'UPDATE' THEN IF OLD.role_id IS DISTINCT FROM NEW.role_id THEN INSERT INTO user_history (user_id, change_type, old_values, new_values, changed_by) VALUES (NEW.id, 'role_change', jsonb_build_object('role_id', OLD.role_id), jsonb_build_object('role_id', NEW.role_id), NEW.updated_by); END IF; IF OLD.status IS DISTINCT FROM NEW.status THEN INSERT INTO user_history (user_id, change_type, old_values, new_values, changed_by) VALUES (NEW.id, 'status_change', jsonb_build_object('status', OLD.status), jsonb_build_object('status', NEW.status), NEW.updated_by); END IF; IF OLD.department_id IS DISTINCT FROM NEW.department_id THEN INSERT INTO user_history (user_id, change_type, old_values, new_values, changed_by) VALUES (NEW.id, 'department_change', jsonb_build_object('department_id', OLD.department_id), jsonb_build_object('department_id', NEW.department_id), NEW.updated_by); END IF; ELSIF TG_OP = 'DELETE' THEN INSERT INTO user_history (user_id, change_type, old_values, new_values, changed_by) VALUES (NULL, 'user_deleted', jsonb_build_object('id', OLD.id), NULL, NULL); END IF; RETURN NULL; END; $$;


ALTER FUNCTION public.track_user_changes() OWNER TO postgres;

--
-- Name: track_vehicle_changes(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.track_vehicle_changes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        INSERT INTO vehicle_history (
            vehicle_id,
            changed_by,
            change_type,
            old_values,
            new_values
        ) VALUES (
            OLD.id,
            NULL, -- TODO: Add user_id when authentication is implemented
            'UPDATE',
            row_to_json(OLD),
            row_to_json(NEW)
        );
    ELSIF TG_OP = 'DELETE' THEN
        INSERT INTO vehicle_history (
            vehicle_id,
            changed_by,
            change_type,
            old_values,
            new_values
        ) VALUES (
            OLD.id,
            NULL, -- TODO: Add user_id when authentication is implemented
            'DELETE',
            row_to_json(OLD),
            NULL
        );
    END IF;
    RETURN NULL;
END;
$$;


ALTER FUNCTION public.track_vehicle_changes() OWNER TO postgres;

--
-- Name: update_fuel_card_history(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_fuel_card_history() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        -- Track changes to important fields
        IF OLD.status_id IS DISTINCT FROM NEW.status_id THEN
            INSERT INTO fuel_card_history (card_id, status_id, notes, created_by)
            VALUES (
                NEW.id,
                NEW.status_id,
                (SELECT description FROM fuel_card_statuses WHERE id = OLD.status_id),
                NEW.created_by
            );
        END IF;

        IF OLD.balance_currency IS DISTINCT FROM NEW.balance_currency THEN
            INSERT INTO fuel_card_history (card_id, notes, created_by)
            VALUES (
                NEW.id,
                'Balance changed from ' || OLD.balance_currency || ' to ' || NEW.balance_currency,
                NEW.created_by
            );
        END IF;

        IF OLD.daily_limit_litres IS DISTINCT FROM NEW.daily_limit_litres THEN
            INSERT INTO fuel_card_history (card_id, notes, created_by)
            VALUES (
                NEW.id,
                'Daily limit changed from ' || OLD.daily_limit_litres || ' to ' || NEW.daily_limit_litres,
                NEW.created_by
            );
        END IF;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_fuel_card_history() OWNER TO postgres;

--
-- Name: update_fuel_transaction_history(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_fuel_transaction_history() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        -- Track changes to important fields
        IF OLD.liters IS DISTINCT FROM NEW.liters THEN
            INSERT INTO fuel_transaction_history (transaction_id, field_name, old_value, new_value, updated_by)
            VALUES (
                NEW.id,
                'liters',
                OLD.liters::TEXT,
                NEW.liters::TEXT,
                NEW.updated_by
            );
        END IF;

        IF OLD.price_per_liter IS DISTINCT FROM NEW.price_per_liter THEN
            INSERT INTO fuel_transaction_history (transaction_id, field_name, old_value, new_value, updated_by)
            VALUES (
                NEW.id,
                'price_per_liter',
                OLD.price_per_liter::TEXT,
                NEW.price_per_liter::TEXT,
                NEW.updated_by
            );
        END IF;

        IF OLD.total_amount IS DISTINCT FROM NEW.total_amount THEN
            INSERT INTO fuel_transaction_history (transaction_id, field_name, old_value, new_value, updated_by)
            VALUES (
                NEW.id,
                'total_amount',
                OLD.total_amount::TEXT,
                NEW.total_amount::TEXT,
                NEW.updated_by
            );
        END IF;

        IF OLD.odometer_reading IS DISTINCT FROM NEW.odometer_reading THEN
            INSERT INTO fuel_transaction_history (transaction_id, field_name, old_value, new_value, updated_by)
            VALUES (
                NEW.id,
                'odometer_reading',
                OLD.odometer_reading::TEXT,
                NEW.odometer_reading::TEXT,
                NEW.updated_by
            );
        END IF;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_fuel_transaction_history() OWNER TO postgres;

--
-- Name: update_last_updated_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_last_updated_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.last_updated = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_last_updated_column() OWNER TO postgres;

--
-- Name: update_trip_distance(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_trip_distance() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.start_location IS NOT NULL AND NEW.end_location IS NOT NULL THEN
        NEW.distance := calculate_distance(NEW.start_location, NEW.end_location);
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_trip_distance() OWNER TO postgres;

--
-- Name: update_trip_history(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_trip_history() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'UPDATE' THEN
        -- Track changes to important fields
        IF OLD.status_id IS DISTINCT FROM NEW.status_id THEN
            INSERT INTO trip_history (trip_id, status_id, notes, created_by)
            VALUES (
                NEW.id,
                NEW.status_id,
                (SELECT description FROM trip_statuses WHERE id = OLD.status_id),
                NEW.created_by
            );
        END IF;

        IF OLD.start_location IS DISTINCT FROM NEW.start_location THEN
            INSERT INTO trip_history (trip_id, notes, created_by)
            VALUES (
                NEW.id,
                OLD.start_location,
                NEW.created_by
            );
        END IF;

        IF OLD.end_location IS DISTINCT FROM NEW.end_location THEN
            INSERT INTO trip_history (trip_id, notes, created_by)
            VALUES (
                NEW.id,
                OLD.end_location,
                NEW.created_by
            );
        END IF;

        IF OLD.end_time IS DISTINCT FROM NEW.end_time THEN
            INSERT INTO trip_history (trip_id, notes, created_by)
            VALUES (
                NEW.id,
                OLD.end_time::TEXT,
                NEW.created_by
            );
        END IF;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_trip_history() OWNER TO postgres;

--
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

--
-- Name: validate_media_upload(character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.validate_media_upload(p_filename character varying, p_mime_type character varying, p_file_size integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_media_type RECORD;
BEGIN
    -- Get media type configuration
    SELECT * INTO v_media_type
    FROM media_types
    WHERE mime_type = p_mime_type;
    
    -- Check if media type exists
    IF v_media_type IS NULL THEN
        RETURN false;
    END IF;
    
    -- Check file size
    IF p_file_size > v_media_type.max_size_bytes THEN
        RETURN false;
    END IF;
    
    -- Check file extension
    IF NOT (LOWER(SPLIT_PART(p_filename, '.', -1)) = ANY(v_media_type.allowed_extensions)) THEN
        RETURN false;
    END IF;
    
    RETURN true;
END;
$$;


ALTER FUNCTION public.validate_media_upload(p_filename character varying, p_mime_type character varying, p_file_size integer) OWNER TO postgres;

--
-- Name: validate_vehicle_import(text, text, text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.validate_vehicle_import(p_make text, p_model text, p_registration text, p_year integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
BEGIN
    -- Check if make can be standardized
    IF standardize_vehicle_make(p_make) IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- Check if model can be standardized for the given make
    IF standardize_vehicle_model(p_model, standardize_vehicle_make(p_make)) IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- Check registration format
    IF p_registration IS NOT NULL AND clean_registration_number(p_registration) !~ '^[A-Z0-9-]+$' THEN
        RETURN FALSE;
    END IF;
    
    -- Check year
    IF p_year < 1900 OR p_year > EXTRACT(YEAR FROM CURRENT_DATE) THEN
        RETURN FALSE;
    END IF;
    
    RETURN TRUE;
END;
$_$;


ALTER FUNCTION public.validate_vehicle_import(p_make text, p_model text, p_registration text, p_year integer) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: access_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.access_requests (
    id integer NOT NULL,
    personal_number character varying(50) NOT NULL,
    email character varying(255) NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    phone character varying(50),
    department_id integer,
    office_id integer,
    role_id integer,
    status character varying(50) DEFAULT 'pending'::character varying,
    processed_by integer,
    processed_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.access_requests OWNER TO postgres;

--
-- Name: access_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.access_requests_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.access_requests_id_seq OWNER TO postgres;

--
-- Name: access_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.access_requests_id_seq OWNED BY public.access_requests.id;


--
-- Name: assignment_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.assignment_statuses (
    id integer NOT NULL,
    name character varying(20) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.assignment_statuses OWNER TO postgres;

--
-- Name: assignment_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.assignment_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.assignment_statuses_id_seq OWNER TO postgres;

--
-- Name: assignment_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.assignment_statuses_id_seq OWNED BY public.assignment_statuses.id;


--
-- Name: assignment_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.assignment_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.assignment_types OWNER TO postgres;

--
-- Name: assignment_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.assignment_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.assignment_types_id_seq OWNER TO postgres;

--
-- Name: assignment_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.assignment_types_id_seq OWNED BY public.assignment_types.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(191) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(191) NOT NULL,
    owner character varying(191) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: departments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departments (
    id integer NOT NULL,
    office_id integer,
    name character varying(200) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.departments OWNER TO postgres;

--
-- Name: departments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departments_id_seq OWNER TO postgres;

--
-- Name: departments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departments_id_seq OWNED BY public.departments.id;


--
-- Name: driver_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_assignments (
    id integer NOT NULL,
    driver_id integer,
    vehicle_id integer,
    assigned_by integer,
    assigned_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    returned_at timestamp with time zone,
    status character varying(50) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.driver_assignments OWNER TO postgres;

--
-- Name: driver_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_assignments_id_seq OWNER TO postgres;

--
-- Name: driver_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_assignments_id_seq OWNED BY public.driver_assignments.id;


--
-- Name: driver_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_history (
    id integer NOT NULL,
    driver_id integer,
    changed_by integer,
    change_type character varying(50),
    old_values jsonb,
    new_values jsonb,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.driver_history OWNER TO postgres;

--
-- Name: driver_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_history_id_seq OWNER TO postgres;

--
-- Name: driver_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_history_id_seq OWNED BY public.driver_history.id;


--
-- Name: driver_interdiction_records; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_interdiction_records (
    id integer NOT NULL,
    driver_id integer,
    interdiction_date date NOT NULL,
    expected_return_date date,
    actual_return_date date,
    interdiction_reason text,
    interdiction_authority character varying(100),
    status character varying(50) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.driver_interdiction_records OWNER TO postgres;

--
-- Name: driver_interdiction_records_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_interdiction_records_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_interdiction_records_id_seq OWNER TO postgres;

--
-- Name: driver_interdiction_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_interdiction_records_id_seq OWNED BY public.driver_interdiction_records.id;


--
-- Name: driver_licenses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_licenses (
    id integer NOT NULL,
    driver_id integer,
    license_number character varying(50) NOT NULL,
    license_type character varying(50) NOT NULL,
    issuing_authority character varying(100) NOT NULL,
    issue_date date NOT NULL,
    expiry_date date NOT NULL,
    restrictions text,
    status character varying(50) DEFAULT 'active'::character varying,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.driver_licenses OWNER TO postgres;

--
-- Name: driver_licenses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_licenses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_licenses_id_seq OWNER TO postgres;

--
-- Name: driver_licenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_licenses_id_seq OWNED BY public.driver_licenses.id;


--
-- Name: driver_status; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_status (
    id integer NOT NULL,
    driver_id integer,
    status_type character varying(50) NOT NULL,
    start_date date NOT NULL,
    end_date date,
    reason text,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.driver_status OWNER TO postgres;

--
-- Name: driver_status_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_status_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_status_id_seq OWNER TO postgres;

--
-- Name: driver_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_status_id_seq OWNED BY public.driver_status.id;


--
-- Name: driver_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_statuses (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.driver_statuses OWNER TO postgres;

--
-- Name: driver_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_statuses_id_seq OWNER TO postgres;

--
-- Name: driver_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_statuses_id_seq OWNED BY public.driver_statuses.id;


--
-- Name: drivers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.drivers (
    id integer NOT NULL,
    user_id integer,
    personal_number character varying(50) NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    phone character varying(50),
    email character varying(255),
    department_id integer,
    office_id integer,
    status_id integer,
    joining_date date NOT NULL,
    emergency_contact_name character varying(100),
    emergency_contact_phone character varying(50),
    emergency_contact_relationship character varying(50),
    blood_type character varying(10),
    medical_conditions text,
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.drivers OWNER TO postgres;

--
-- Name: drivers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.drivers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.drivers_id_seq OWNER TO postgres;

--
-- Name: drivers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.drivers_id_seq OWNED BY public.drivers.id;


--
-- Name: fuel_card_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_card_history (
    id integer NOT NULL,
    card_id integer,
    status_id integer,
    notes text,
    created_by integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_card_history OWNER TO postgres;

--
-- Name: fuel_card_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_card_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_card_history_id_seq OWNER TO postgres;

--
-- Name: fuel_card_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_card_history_id_seq OWNED BY public.fuel_card_history.id;


--
-- Name: fuel_card_providers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_card_providers (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_card_providers OWNER TO postgres;

--
-- Name: fuel_card_providers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_card_providers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_card_providers_id_seq OWNER TO postgres;

--
-- Name: fuel_card_providers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_card_providers_id_seq OWNED BY public.fuel_card_providers.id;


--
-- Name: fuel_card_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_card_statuses (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_card_statuses OWNER TO postgres;

--
-- Name: fuel_card_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_card_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_card_statuses_id_seq OWNER TO postgres;

--
-- Name: fuel_card_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_card_statuses_id_seq OWNED BY public.fuel_card_statuses.id;


--
-- Name: fuel_card_transactions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_card_transactions (
    id integer NOT NULL,
    fuel_card_id integer,
    transaction_date date NOT NULL,
    amount_currency numeric(10,2),
    amount_litres numeric(10,2),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_card_transactions OWNER TO postgres;

--
-- Name: fuel_card_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_card_transactions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_card_transactions_id_seq OWNER TO postgres;

--
-- Name: fuel_card_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_card_transactions_id_seq OWNED BY public.fuel_card_transactions.id;


--
-- Name: fuel_card_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_card_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_card_types OWNER TO postgres;

--
-- Name: fuel_card_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_card_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_card_types_id_seq OWNER TO postgres;

--
-- Name: fuel_card_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_card_types_id_seq OWNED BY public.fuel_card_types.id;


--
-- Name: fuel_cards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_cards (
    id integer NOT NULL,
    card_number character varying(50) NOT NULL,
    card_type character varying(50),
    issue_date date,
    expiry_date date,
    daily_limit numeric(10,2),
    monthly_limit numeric(10,2),
    status character varying(20) DEFAULT 'active'::character varying,
    assigned_driver_id integer,
    assigned_vehicle_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fuel_type character varying(20),
    service_provider character varying(100),
    current_liters numeric(10,2),
    current_balance numeric(10,2),
    service_provider_id integer,
    fuel_type_id integer
);


ALTER TABLE public.fuel_cards OWNER TO postgres;

--
-- Name: fuel_cards_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_cards_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_cards_id_seq OWNER TO postgres;

--
-- Name: fuel_cards_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_cards_id_seq OWNED BY public.fuel_cards.id;


--
-- Name: fuel_records; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_records (
    id integer NOT NULL,
    vehicle_id integer,
    driver_id integer,
    fuel_card_id integer,
    liters numeric(10,2) NOT NULL,
    cost numeric(10,2) NOT NULL,
    odometer_reading numeric(10,2),
    fuel_station character varying(255),
    transaction_date timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_records OWNER TO postgres;

--
-- Name: fuel_records_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_records_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_records_id_seq OWNER TO postgres;

--
-- Name: fuel_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_records_id_seq OWNED BY public.fuel_records.id;


--
-- Name: fuel_stations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_stations (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    location character varying(255),
    contact_number character varying(20),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_stations OWNER TO postgres;

--
-- Name: fuel_stations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_stations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_stations_id_seq OWNER TO postgres;

--
-- Name: fuel_stations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_stations_id_seq OWNED BY public.fuel_stations.id;


--
-- Name: fuel_transaction_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_transaction_history (
    id integer NOT NULL,
    transaction_id integer,
    field_name character varying(50) NOT NULL,
    old_value text,
    new_value text,
    updated_by integer,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_transaction_history OWNER TO postgres;

--
-- Name: fuel_transaction_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_transaction_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_transaction_history_id_seq OWNER TO postgres;

--
-- Name: fuel_transaction_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_transaction_history_id_seq OWNED BY public.fuel_transaction_history.id;


--
-- Name: fuel_transactions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_transactions (
    id integer NOT NULL,
    card_id integer,
    vehicle_id integer,
    driver_id integer,
    station_id integer,
    transaction_date timestamp without time zone NOT NULL,
    liters numeric(10,2) NOT NULL,
    price_per_liter numeric(10,2) NOT NULL,
    total_amount numeric(10,2) NOT NULL,
    odometer_reading integer,
    trip_id integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_transactions OWNER TO postgres;

--
-- Name: fuel_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_transactions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_transactions_id_seq OWNER TO postgres;

--
-- Name: fuel_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_transactions_id_seq OWNED BY public.fuel_transactions.id;


--
-- Name: fuel_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_types (
    id integer NOT NULL,
    name character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fuel_types OWNER TO postgres;

--
-- Name: fuel_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_types_id_seq OWNER TO postgres;

--
-- Name: fuel_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_types_id_seq OWNED BY public.fuel_types.id;


--
-- Name: general_fuel_card_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.general_fuel_card_assignments (
    id integer NOT NULL,
    department_id integer,
    fuel_card_id integer,
    assigned_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    unassigned_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.general_fuel_card_assignments OWNER TO postgres;

--
-- Name: general_fuel_card_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.general_fuel_card_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.general_fuel_card_assignments_id_seq OWNER TO postgres;

--
-- Name: general_fuel_card_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.general_fuel_card_assignments_id_seq OWNED BY public.general_fuel_card_assignments.id;


--
-- Name: incident_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_history (
    id integer NOT NULL,
    incident_id integer,
    action character varying(50) NOT NULL,
    notes text,
    created_by_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_history OWNER TO postgres;

--
-- Name: incident_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_history_id_seq OWNER TO postgres;

--
-- Name: incident_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_history_id_seq OWNED BY public.incident_history.id;


--
-- Name: incident_photos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_photos (
    id integer NOT NULL,
    incident_id integer,
    photo_path character varying(255) NOT NULL,
    description text,
    uploaded_by_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_photos OWNER TO postgres;

--
-- Name: incident_photos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_photos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_photos_id_seq OWNER TO postgres;

--
-- Name: incident_photos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_photos_id_seq OWNED BY public.incident_photos.id;


--
-- Name: incident_repairs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_repairs (
    id integer NOT NULL,
    incident_id integer,
    service_provider_id integer,
    repair_status character varying(30) NOT NULL,
    estimated_completion_date date,
    actual_completion_date date,
    parts_cost numeric(10,2),
    labor_cost numeric(10,2),
    other_costs numeric(10,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_repairs OWNER TO postgres;

--
-- Name: incident_repairs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_repairs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_repairs_id_seq OWNER TO postgres;

--
-- Name: incident_repairs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_repairs_id_seq OWNED BY public.incident_repairs.id;


--
-- Name: incident_severities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_severities (
    id integer NOT NULL,
    name character varying(20) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_severities OWNER TO postgres;

--
-- Name: incident_severities_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_severities_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_severities_id_seq OWNER TO postgres;

--
-- Name: incident_severities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_severities_id_seq OWNED BY public.incident_severities.id;


--
-- Name: incident_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_statuses (
    id integer NOT NULL,
    name character varying(30) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_statuses OWNER TO postgres;

--
-- Name: incident_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_statuses_id_seq OWNER TO postgres;

--
-- Name: incident_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_statuses_id_seq OWNED BY public.incident_statuses.id;


--
-- Name: incident_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_types OWNER TO postgres;

--
-- Name: incident_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_types_id_seq OWNER TO postgres;

--
-- Name: incident_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_types_id_seq OWNED BY public.incident_types.id;


--
-- Name: incident_witnesses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incident_witnesses (
    id integer NOT NULL,
    incident_id integer,
    name character varying(100) NOT NULL,
    contact character varying(50),
    statement text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incident_witnesses OWNER TO postgres;

--
-- Name: incident_witnesses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incident_witnesses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_witnesses_id_seq OWNER TO postgres;

--
-- Name: incident_witnesses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incident_witnesses_id_seq OWNED BY public.incident_witnesses.id;


--
-- Name: incidents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incidents (
    id integer NOT NULL,
    vehicle_id integer,
    driver_id integer,
    incident_type_id integer,
    severity_id integer,
    status_id integer,
    insurance_provider_id integer,
    incident_date date NOT NULL,
    incident_time time without time zone NOT NULL,
    location text NOT NULL,
    description text NOT NULL,
    injuries text,
    damage_cost numeric(10,2),
    insurance_claim_number character varying(50),
    police_report_number character varying(50),
    reported_by_id integer,
    assigned_to_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.incidents OWNER TO postgres;

--
-- Name: incidents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incidents_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incidents_id_seq OWNER TO postgres;

--
-- Name: incidents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incidents_id_seq OWNED BY public.incidents.id;


--
-- Name: insurance_providers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insurance_providers (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    contact_person character varying(100),
    phone character varying(20),
    email character varying(100),
    address text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.insurance_providers OWNER TO postgres;

--
-- Name: insurance_providers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.insurance_providers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.insurance_providers_id_seq OWNER TO postgres;

--
-- Name: insurance_providers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.insurance_providers_id_seq OWNED BY public.insurance_providers.id;


--
-- Name: maintenance_alerts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_alerts (
    id integer NOT NULL,
    vehicle_id integer,
    alert_type character varying(50) NOT NULL,
    description text NOT NULL,
    severity character varying(20) DEFAULT 'medium'::character varying NOT NULL,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    due_date timestamp with time zone,
    resolved_at timestamp with time zone,
    resolved_by integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_alerts OWNER TO postgres;

--
-- Name: maintenance_alerts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_alerts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_alerts_id_seq OWNER TO postgres;

--
-- Name: maintenance_alerts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_alerts_id_seq OWNED BY public.maintenance_alerts.id;


--
-- Name: maintenance_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_history (
    id integer NOT NULL,
    maintenance_record_id integer,
    action character varying(50) NOT NULL,
    notes text,
    created_by_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_history OWNER TO postgres;

--
-- Name: maintenance_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_history_id_seq OWNER TO postgres;

--
-- Name: maintenance_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_history_id_seq OWNED BY public.maintenance_history.id;


--
-- Name: maintenance_records; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_records (
    id integer NOT NULL,
    vehicle_id integer,
    maintenance_type_id integer,
    service_provider_id integer,
    status_id integer,
    service_date date NOT NULL,
    next_service_date date,
    odometer_reading integer,
    cost numeric(10,2),
    description text,
    reported_by_id integer,
    assigned_technician_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_records OWNER TO postgres;

--
-- Name: maintenance_records_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_records_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_records_id_seq OWNER TO postgres;

--
-- Name: maintenance_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_records_id_seq OWNED BY public.maintenance_records.id;


--
-- Name: maintenance_schedule; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_schedule (
    id integer NOT NULL,
    vehicle_id integer,
    maintenance_type character varying(50) NOT NULL,
    description text,
    scheduled_date timestamp with time zone NOT NULL,
    next_maintenance_date timestamp with time zone,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    cost numeric(10,2),
    completed_at timestamp with time zone,
    completed_by integer,
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_schedule OWNER TO postgres;

--
-- Name: maintenance_schedule_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_schedule_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_schedule_id_seq OWNER TO postgres;

--
-- Name: maintenance_schedule_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_schedule_id_seq OWNED BY public.maintenance_schedule.id;


--
-- Name: maintenance_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_statuses (
    id integer NOT NULL,
    name character varying(20) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_statuses OWNER TO postgres;

--
-- Name: maintenance_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_statuses_id_seq OWNER TO postgres;

--
-- Name: maintenance_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_statuses_id_seq OWNED BY public.maintenance_statuses.id;


--
-- Name: maintenance_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_types OWNER TO postgres;

--
-- Name: maintenance_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_types_id_seq OWNER TO postgres;

--
-- Name: maintenance_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_types_id_seq OWNED BY public.maintenance_types.id;


--
-- Name: media_relations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.media_relations (
    id integer NOT NULL,
    media_id integer,
    related_table character varying(50) NOT NULL,
    related_id integer NOT NULL,
    relation_type character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.media_relations OWNER TO postgres;

--
-- Name: media_relations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.media_relations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.media_relations_id_seq OWNER TO postgres;

--
-- Name: media_relations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.media_relations_id_seq OWNED BY public.media_relations.id;


--
-- Name: media_storage; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.media_storage (
    id integer NOT NULL,
    original_filename character varying(255) NOT NULL,
    storage_path character varying(255) NOT NULL,
    file_size_bytes integer NOT NULL,
    mime_type character varying(100) NOT NULL,
    media_type_id integer,
    uploaded_by_id integer,
    is_public boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.media_storage OWNER TO postgres;

--
-- Name: media_storage_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.media_storage_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.media_storage_id_seq OWNER TO postgres;

--
-- Name: media_storage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.media_storage_id_seq OWNED BY public.media_storage.id;


--
-- Name: media_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.media_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    mime_type character varying(100) NOT NULL,
    max_size_bytes integer NOT NULL,
    allowed_extensions text[] NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.media_types OWNER TO postgres;

--
-- Name: media_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.media_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.media_types_id_seq OWNER TO postgres;

--
-- Name: media_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.media_types_id_seq OWNED BY public.media_types.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: odometer_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.odometer_history (
    id integer NOT NULL,
    vehicle_id integer,
    odometer_reading integer NOT NULL,
    date_recorded date NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.odometer_history OWNER TO postgres;

--
-- Name: odometer_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.odometer_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.odometer_history_id_seq OWNER TO postgres;

--
-- Name: odometer_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.odometer_history_id_seq OWNED BY public.odometer_history.id;


--
-- Name: office_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.office_assignments (
    id integer NOT NULL,
    office_id integer,
    vehicle_id integer,
    assigned_by integer,
    assigned_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    returned_at timestamp with time zone,
    status character varying(50) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.office_assignments OWNER TO postgres;

--
-- Name: office_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.office_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.office_assignments_id_seq OWNER TO postgres;

--
-- Name: office_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.office_assignments_id_seq OWNED BY public.office_assignments.id;


--
-- Name: office_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.office_history (
    id integer NOT NULL,
    office_id integer,
    changed_by integer,
    change_type character varying(50),
    old_values jsonb,
    new_values jsonb,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.office_history OWNER TO postgres;

--
-- Name: office_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.office_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.office_history_id_seq OWNER TO postgres;

--
-- Name: office_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.office_history_id_seq OWNED BY public.office_history.id;


--
-- Name: offices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.offices (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    title character varying(255),
    department_id integer,
    contact_person character varying(255),
    contact_email character varying(255),
    contact_phone character varying(50),
    location character varying(255),
    status character varying(50) DEFAULT 'active'::character varying,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.offices OWNER TO postgres;

--
-- Name: offices_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.offices_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.offices_id_seq OWNER TO postgres;

--
-- Name: offices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.offices_id_seq OWNED BY public.offices.id;


--
-- Name: password_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_history (
    id integer NOT NULL,
    user_id integer,
    password_hash character varying(255) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.password_history OWNER TO postgres;

--
-- Name: password_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.password_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.password_history_id_seq OWNER TO postgres;

--
-- Name: password_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.password_history_id_seq OWNED BY public.password_history.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_resets (
    id integer NOT NULL,
    user_id integer,
    token character varying(255) NOT NULL,
    status character varying(50) DEFAULT 'pending'::character varying,
    processed_by integer,
    processed_at timestamp with time zone,
    expires_at timestamp with time zone NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    notes text
);


ALTER TABLE public.password_resets OWNER TO postgres;

--
-- Name: password_resets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.password_resets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.password_resets_id_seq OWNER TO postgres;

--
-- Name: password_resets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.password_resets_id_seq OWNED BY public.password_resets.id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    module character varying(50) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: plant_equipment_fuel_card_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.plant_equipment_fuel_card_assignments (
    id integer NOT NULL,
    equipment_name character varying(100) NOT NULL,
    fuel_card_id integer,
    assigned_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    unassigned_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.plant_equipment_fuel_card_assignments OWNER TO postgres;

--
-- Name: plant_equipment_fuel_card_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.plant_equipment_fuel_card_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.plant_equipment_fuel_card_assignments_id_seq OWNER TO postgres;

--
-- Name: plant_equipment_fuel_card_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.plant_equipment_fuel_card_assignments_id_seq OWNED BY public.plant_equipment_fuel_card_assignments.id;


--
-- Name: rate_limits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rate_limits (
    id integer NOT NULL,
    key character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.rate_limits OWNER TO postgres;

--
-- Name: rate_limits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rate_limits_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rate_limits_id_seq OWNER TO postgres;

--
-- Name: rate_limits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rate_limits_id_seq OWNED BY public.rate_limits.id;


--
-- Name: report_executions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report_executions (
    id integer NOT NULL,
    saved_report_id integer,
    schedule_id integer,
    parameters jsonb NOT NULL,
    status character varying(50) NOT NULL,
    result_file_path character varying(255),
    error_message text,
    started_at timestamp with time zone NOT NULL,
    completed_at timestamp with time zone,
    created_by integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.report_executions OWNER TO postgres;

--
-- Name: report_executions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_executions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_executions_id_seq OWNER TO postgres;

--
-- Name: report_executions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_executions_id_seq OWNED BY public.report_executions.id;


--
-- Name: report_parameters; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report_parameters (
    id integer NOT NULL,
    report_type_id integer,
    name character varying(50) NOT NULL,
    data_type character varying(50) NOT NULL,
    is_required boolean DEFAULT false,
    default_value text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.report_parameters OWNER TO postgres;

--
-- Name: report_parameters_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_parameters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_parameters_id_seq OWNER TO postgres;

--
-- Name: report_parameters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_parameters_id_seq OWNED BY public.report_parameters.id;


--
-- Name: report_schedules; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report_schedules (
    id integer NOT NULL,
    saved_report_id integer,
    schedule_type character varying(50) NOT NULL,
    schedule_time time without time zone,
    schedule_day character varying(20),
    recipients jsonb NOT NULL,
    last_run timestamp with time zone,
    next_run timestamp with time zone,
    is_active boolean DEFAULT true,
    created_by integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.report_schedules OWNER TO postgres;

--
-- Name: report_schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_schedules_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_schedules_id_seq OWNER TO postgres;

--
-- Name: report_schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_schedules_id_seq OWNED BY public.report_schedules.id;


--
-- Name: report_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    category character varying(50) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.report_types OWNER TO postgres;

--
-- Name: report_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_types_id_seq OWNER TO postgres;

--
-- Name: report_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_types_id_seq OWNED BY public.report_types.id;


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_permissions (
    role_id integer NOT NULL,
    permission_id integer NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.role_permissions OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: saved_reports; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.saved_reports (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    report_type_id integer,
    parameters jsonb NOT NULL,
    created_by integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.saved_reports OWNER TO postgres;

--
-- Name: saved_reports_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.saved_reports_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.saved_reports_id_seq OWNER TO postgres;

--
-- Name: saved_reports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.saved_reports_id_seq OWNED BY public.saved_reports.id;


--
-- Name: service_providers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.service_providers (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    maintenance_specialties text[],
    service_rating numeric(3,2),
    is_active boolean DEFAULT true
);


ALTER TABLE public.service_providers OWNER TO postgres;

--
-- Name: service_providers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.service_providers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.service_providers_id_seq OWNER TO postgres;

--
-- Name: service_providers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.service_providers_id_seq OWNED BY public.service_providers.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: vehicles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicles (
    id integer NOT NULL,
    registration_no character varying(20) NOT NULL,
    financed_by character varying(100),
    engine_no character varying(50),
    chassis_no character varying(50),
    tag_number character varying(50),
    make_model character varying(100),
    year_of_purchase date,
    pv_number character varying(50),
    original_location character varying(100),
    current_location character varying(100),
    replacement_date date,
    amount numeric(15,2),
    depreciation_rate numeric(15,2),
    annual_depreciation numeric(15,2),
    accumulated_depreciation numeric(15,2),
    net_book_value numeric(15,2),
    disposal_date date,
    disposal_value numeric(15,2),
    responsible_officer character varying(100),
    asset_condition character varying(50),
    has_logbook boolean,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    office_id integer,
    department_id integer,
    assigned_user_id integer,
    make character varying(100),
    model character varying(100),
    current_odometer integer,
    last_maintenance date,
    next_maintenance date,
    fuel_tank_capacity numeric(10,2),
    fuel_type character varying(50),
    status character varying(20) DEFAULT 'Active'::character varying,
    type_id integer,
    status_id integer,
    initial_mileage integer DEFAULT 0,
    utilization_rate numeric(5,2) DEFAULT 0,
    CONSTRAINT valid_fuel_capacity CHECK ((fuel_tank_capacity > (0)::numeric)),
    CONSTRAINT valid_fuel_type CHECK (((fuel_type)::text = ANY ((ARRAY['Petrol'::character varying, 'Diesel'::character varying, 'Hybrid'::character varying, 'Electric'::character varying])::text[]))),
    CONSTRAINT valid_make CHECK ((make IS NOT NULL)),
    CONSTRAINT valid_model CHECK ((model IS NOT NULL)),
    CONSTRAINT valid_odometer CHECK ((current_odometer >= 0)),
    CONSTRAINT valid_year CHECK (((EXTRACT(year FROM year_of_purchase) >= (1900)::numeric) AND (EXTRACT(year FROM year_of_purchase) <= EXTRACT(year FROM CURRENT_DATE)))),
    CONSTRAINT vehicles_status_check CHECK (((status)::text = ANY ((ARRAY['Active'::character varying, 'Inactive'::character varying, 'Maintenance'::character varying, 'Disposed'::character varying])::text[])))
);


ALTER TABLE public.vehicles OWNER TO postgres;

--
-- Name: standardized_vehicles; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.standardized_vehicles AS
 SELECT id,
    public.standardize_vehicle_make((make)::text) AS standardized_make,
    public.standardize_vehicle_model((model)::text, public.standardize_vehicle_make((make)::text)) AS standardized_model,
    registration_no,
    year_of_purchase,
    current_odometer,
    last_maintenance,
    next_maintenance,
    fuel_tank_capacity,
    fuel_type,
    status,
    created_at,
    updated_at
   FROM public.vehicles v;


ALTER VIEW public.standardized_vehicles OWNER TO postgres;

--
-- Name: trip_checkpoints; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_checkpoints (
    id integer NOT NULL,
    trip_id integer,
    location character varying(255) NOT NULL,
    arrival_time timestamp with time zone,
    departure_time timestamp with time zone,
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_checkpoints OWNER TO postgres;

--
-- Name: trip_checkpoints_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_checkpoints_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_checkpoints_id_seq OWNER TO postgres;

--
-- Name: trip_checkpoints_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_checkpoints_id_seq OWNED BY public.trip_checkpoints.id;


--
-- Name: trip_expenses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_expenses (
    id integer NOT NULL,
    trip_id integer,
    category character varying(50) NOT NULL,
    amount numeric(10,2) NOT NULL,
    description text,
    receipt_number character varying(50),
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_expenses OWNER TO postgres;

--
-- Name: trip_expenses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_expenses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_expenses_id_seq OWNER TO postgres;

--
-- Name: trip_expenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_expenses_id_seq OWNED BY public.trip_expenses.id;


--
-- Name: trip_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_history (
    id integer NOT NULL,
    trip_id integer,
    field_name character varying(50) NOT NULL,
    old_value text,
    new_value text,
    updated_by integer,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_history OWNER TO postgres;

--
-- Name: trip_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_history_id_seq OWNER TO postgres;

--
-- Name: trip_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_history_id_seq OWNED BY public.trip_history.id;


--
-- Name: trip_passengers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_passengers (
    id integer NOT NULL,
    trip_id integer,
    name character varying(100) NOT NULL,
    department character varying(100),
    contact character varying(50),
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_passengers OWNER TO postgres;

--
-- Name: trip_passengers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_passengers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_passengers_id_seq OWNER TO postgres;

--
-- Name: trip_passengers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_passengers_id_seq OWNED BY public.trip_passengers.id;


--
-- Name: trip_purposes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_purposes (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_purposes OWNER TO postgres;

--
-- Name: trip_purposes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_purposes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_purposes_id_seq OWNER TO postgres;

--
-- Name: trip_purposes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_purposes_id_seq OWNED BY public.trip_purposes.id;


--
-- Name: trip_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_statuses (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_statuses OWNER TO postgres;

--
-- Name: trip_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_statuses_id_seq OWNER TO postgres;

--
-- Name: trip_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_statuses_id_seq OWNED BY public.trip_statuses.id;


--
-- Name: trip_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_types OWNER TO postgres;

--
-- Name: trip_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_types_id_seq OWNER TO postgres;

--
-- Name: trip_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_types_id_seq OWNED BY public.trip_types.id;


--
-- Name: trips; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trips (
    id integer NOT NULL,
    vehicle_id integer,
    driver_id integer,
    start_location public.geometry(Point,4326),
    end_location public.geometry(Point,4326),
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone,
    status_id integer,
    purpose_id integer,
    distance numeric(10,2),
    fuel_used_litres numeric(10,2),
    notes text,
    created_by integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trips OWNER TO postgres;

--
-- Name: trips_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trips_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trips_id_seq OWNER TO postgres;

--
-- Name: trips_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trips_id_seq OWNED BY public.trips.id;


--
-- Name: user_activity; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_activity (
    id integer NOT NULL,
    user_id integer,
    activity_type character varying(50) NOT NULL,
    ip_address character varying(45),
    user_agent text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_activity OWNER TO postgres;

--
-- Name: user_activity_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_activity_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_activity_id_seq OWNER TO postgres;

--
-- Name: user_activity_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_activity_id_seq OWNED BY public.user_activity.id;


--
-- Name: user_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_history (
    id integer NOT NULL,
    user_id integer,
    changed_by integer,
    change_type character varying(50),
    old_values jsonb,
    new_values jsonb,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    field_name character varying(50) NOT NULL
);


ALTER TABLE public.user_history OWNER TO postgres;

--
-- Name: user_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_history_id_seq OWNER TO postgres;

--
-- Name: user_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_history_id_seq OWNED BY public.user_history.id;


--
-- Name: user_sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_sessions (
    id integer NOT NULL,
    user_id integer,
    token character varying(255) NOT NULL,
    ip_address character varying(45),
    user_agent text,
    expires_at timestamp with time zone NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_sessions OWNER TO postgres;

--
-- Name: user_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_sessions_id_seq OWNER TO postgres;

--
-- Name: user_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_sessions_id_seq OWNED BY public.user_sessions.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    personal_number character varying(50) NOT NULL,
    first_name character varying(100),
    last_name character varying(100),
    phone character varying(50),
    role_id integer,
    department_id integer,
    office_id integer,
    status character varying(50) DEFAULT 'active'::character varying,
    is_temporary_password boolean DEFAULT false,
    last_login timestamp with time zone,
    login_attempts integer DEFAULT 0,
    locked_until timestamp with time zone,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    remember_token character varying(255),
    deactivation_reason text,
    deactivation_date timestamp with time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vehicle_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_assignments (
    id integer NOT NULL,
    vehicle_id integer,
    driver_id integer,
    assignment_date date NOT NULL,
    return_date date,
    assignment_type character varying(50),
    status character varying(20) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    office_id integer,
    department_id integer,
    user_id integer,
    start_location character varying(200),
    end_location character varying(200),
    reason text,
    last_updated timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    assignment_type_id integer,
    status_id integer,
    assigned_by_id integer,
    CONSTRAINT valid_assignment_status CHECK (((status)::text = ANY ((ARRAY['Active'::character varying, 'Completed'::character varying, 'Cancelled'::character varying])::text[]))),
    CONSTRAINT valid_assignment_type CHECK (((assignment_type)::text = ANY ((ARRAY['Primary Deployment'::character varying, 'Temporary Assignment'::character varying, 'Pool Assignment'::character varying])::text[])))
);


ALTER TABLE public.vehicle_assignments OWNER TO postgres;

--
-- Name: vehicle_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_assignments_id_seq OWNER TO postgres;

--
-- Name: vehicle_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_assignments_id_seq OWNED BY public.vehicle_assignments.id;


--
-- Name: vehicle_fuel_card_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_fuel_card_assignments (
    id integer NOT NULL,
    vehicle_id integer,
    fuel_card_id integer,
    assigned_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    unassigned_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_fuel_card_assignments OWNER TO postgres;

--
-- Name: vehicle_fuel_card_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_fuel_card_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_fuel_card_assignments_id_seq OWNER TO postgres;

--
-- Name: vehicle_fuel_card_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_fuel_card_assignments_id_seq OWNED BY public.vehicle_fuel_card_assignments.id;


--
-- Name: vehicle_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_history (
    id integer NOT NULL,
    vehicle_id integer,
    changed_by integer,
    change_type character varying(50),
    old_values jsonb,
    new_values jsonb,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_history OWNER TO postgres;

--
-- Name: vehicle_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_history_id_seq OWNER TO postgres;

--
-- Name: vehicle_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_history_id_seq OWNED BY public.vehicle_history.id;


--
-- Name: vehicle_insurance; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_insurance (
    id integer NOT NULL,
    vehicle_id integer,
    policy_number character varying(50) NOT NULL,
    insurance_company character varying(100) NOT NULL,
    coverage_type character varying(50) NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    premium_amount numeric(10,2) NOT NULL,
    status character varying(50) DEFAULT 'active'::character varying,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_insurance OWNER TO postgres;

--
-- Name: vehicle_insurance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_insurance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_insurance_id_seq OWNER TO postgres;

--
-- Name: vehicle_insurance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_insurance_id_seq OWNED BY public.vehicle_insurance.id;


--
-- Name: vehicle_locations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_locations (
    vehicle_id integer NOT NULL,
    latitude numeric(10,8) NOT NULL,
    longitude numeric(11,8) NOT NULL,
    last_updated timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT valid_latitude CHECK (((latitude >= ('-90'::integer)::numeric) AND (latitude <= (90)::numeric))),
    CONSTRAINT valid_longitude CHECK (((longitude >= ('-180'::integer)::numeric) AND (longitude <= (180)::numeric)))
);


ALTER TABLE public.vehicle_locations OWNER TO postgres;

--
-- Name: vehicle_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_logs (
    id integer NOT NULL,
    vehicle_id integer,
    driver_id integer,
    start_location character varying(255),
    end_location character varying(255),
    trip_start timestamp with time zone,
    trip_end timestamp with time zone,
    status character varying(20) DEFAULT 'in_progress'::character varying NOT NULL,
    fuel_consumed numeric(10,2),
    distance_covered numeric(10,2),
    notes text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_logs OWNER TO postgres;

--
-- Name: vehicle_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_logs_id_seq OWNER TO postgres;

--
-- Name: vehicle_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_logs_id_seq OWNED BY public.vehicle_logs.id;


--
-- Name: vehicle_maintenance; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_maintenance (
    id integer NOT NULL,
    vehicle_id integer,
    maintenance_type character varying(50) NOT NULL,
    description text,
    service_provider character varying(100),
    cost numeric(10,2),
    mileage integer,
    maintenance_date date NOT NULL,
    next_service_date date,
    status character varying(50) DEFAULT 'completed'::character varying,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_maintenance OWNER TO postgres;

--
-- Name: vehicle_maintenance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_maintenance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_maintenance_id_seq OWNER TO postgres;

--
-- Name: vehicle_maintenance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_maintenance_id_seq OWNED BY public.vehicle_maintenance.id;


--
-- Name: vehicle_permits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_permits (
    id integer NOT NULL,
    vehicle_id integer,
    permit_type character varying(50) NOT NULL,
    permit_number character varying(50) NOT NULL,
    issuing_authority character varying(100) NOT NULL,
    issue_date date NOT NULL,
    expiry_date date NOT NULL,
    status character varying(50) DEFAULT 'active'::character varying,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_permits OWNER TO postgres;

--
-- Name: vehicle_permits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_permits_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_permits_id_seq OWNER TO postgres;

--
-- Name: vehicle_permits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_permits_id_seq OWNED BY public.vehicle_permits.id;


--
-- Name: vehicle_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_statuses (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_statuses OWNER TO postgres;

--
-- Name: vehicle_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_statuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_statuses_id_seq OWNER TO postgres;

--
-- Name: vehicle_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_statuses_id_seq OWNED BY public.vehicle_statuses.id;


--
-- Name: vehicle_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_types (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_types OWNER TO postgres;

--
-- Name: vehicle_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_types_id_seq OWNER TO postgres;

--
-- Name: vehicle_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_types_id_seq OWNED BY public.vehicle_types.id;


--
-- Name: vehicle_utilization; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_utilization (
    id integer NOT NULL,
    vehicle_id integer,
    date date NOT NULL,
    utilization_rate numeric(5,2) NOT NULL,
    maintenance_hours numeric(10,2) DEFAULT 0 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_utilization OWNER TO postgres;

--
-- Name: vehicle_utilization_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_utilization_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_utilization_id_seq OWNER TO postgres;

--
-- Name: vehicle_utilization_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_utilization_id_seq OWNED BY public.vehicle_utilization.id;


--
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicles_id_seq OWNER TO postgres;

--
-- Name: vehicles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicles_id_seq OWNED BY public.vehicles.id;


--
-- Name: access_requests id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_requests ALTER COLUMN id SET DEFAULT nextval('public.access_requests_id_seq'::regclass);


--
-- Name: assignment_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assignment_statuses ALTER COLUMN id SET DEFAULT nextval('public.assignment_statuses_id_seq'::regclass);


--
-- Name: assignment_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assignment_types ALTER COLUMN id SET DEFAULT nextval('public.assignment_types_id_seq'::regclass);


--
-- Name: departments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments ALTER COLUMN id SET DEFAULT nextval('public.departments_id_seq'::regclass);


--
-- Name: driver_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_assignments ALTER COLUMN id SET DEFAULT nextval('public.driver_assignments_id_seq'::regclass);


--
-- Name: driver_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_history ALTER COLUMN id SET DEFAULT nextval('public.driver_history_id_seq'::regclass);


--
-- Name: driver_interdiction_records id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_interdiction_records ALTER COLUMN id SET DEFAULT nextval('public.driver_interdiction_records_id_seq'::regclass);


--
-- Name: driver_licenses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses ALTER COLUMN id SET DEFAULT nextval('public.driver_licenses_id_seq'::regclass);


--
-- Name: driver_status id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_status ALTER COLUMN id SET DEFAULT nextval('public.driver_status_id_seq'::regclass);


--
-- Name: driver_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_statuses ALTER COLUMN id SET DEFAULT nextval('public.driver_statuses_id_seq'::regclass);


--
-- Name: drivers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers ALTER COLUMN id SET DEFAULT nextval('public.drivers_id_seq'::regclass);


--
-- Name: fuel_card_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_history ALTER COLUMN id SET DEFAULT nextval('public.fuel_card_history_id_seq'::regclass);


--
-- Name: fuel_card_providers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_providers ALTER COLUMN id SET DEFAULT nextval('public.fuel_card_providers_id_seq'::regclass);


--
-- Name: fuel_card_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_statuses ALTER COLUMN id SET DEFAULT nextval('public.fuel_card_statuses_id_seq'::regclass);


--
-- Name: fuel_card_transactions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_transactions ALTER COLUMN id SET DEFAULT nextval('public.fuel_card_transactions_id_seq'::regclass);


--
-- Name: fuel_card_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_types ALTER COLUMN id SET DEFAULT nextval('public.fuel_card_types_id_seq'::regclass);


--
-- Name: fuel_cards id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards ALTER COLUMN id SET DEFAULT nextval('public.fuel_cards_id_seq'::regclass);


--
-- Name: fuel_records id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_records ALTER COLUMN id SET DEFAULT nextval('public.fuel_records_id_seq'::regclass);


--
-- Name: fuel_stations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_stations ALTER COLUMN id SET DEFAULT nextval('public.fuel_stations_id_seq'::regclass);


--
-- Name: fuel_transaction_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transaction_history ALTER COLUMN id SET DEFAULT nextval('public.fuel_transaction_history_id_seq'::regclass);


--
-- Name: fuel_transactions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transactions ALTER COLUMN id SET DEFAULT nextval('public.fuel_transactions_id_seq'::regclass);


--
-- Name: fuel_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_types ALTER COLUMN id SET DEFAULT nextval('public.fuel_types_id_seq'::regclass);


--
-- Name: general_fuel_card_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.general_fuel_card_assignments ALTER COLUMN id SET DEFAULT nextval('public.general_fuel_card_assignments_id_seq'::regclass);


--
-- Name: incident_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_history ALTER COLUMN id SET DEFAULT nextval('public.incident_history_id_seq'::regclass);


--
-- Name: incident_photos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_photos ALTER COLUMN id SET DEFAULT nextval('public.incident_photos_id_seq'::regclass);


--
-- Name: incident_repairs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_repairs ALTER COLUMN id SET DEFAULT nextval('public.incident_repairs_id_seq'::regclass);


--
-- Name: incident_severities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_severities ALTER COLUMN id SET DEFAULT nextval('public.incident_severities_id_seq'::regclass);


--
-- Name: incident_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_statuses ALTER COLUMN id SET DEFAULT nextval('public.incident_statuses_id_seq'::regclass);


--
-- Name: incident_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_types ALTER COLUMN id SET DEFAULT nextval('public.incident_types_id_seq'::regclass);


--
-- Name: incident_witnesses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_witnesses ALTER COLUMN id SET DEFAULT nextval('public.incident_witnesses_id_seq'::regclass);


--
-- Name: incidents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents ALTER COLUMN id SET DEFAULT nextval('public.incidents_id_seq'::regclass);


--
-- Name: insurance_providers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insurance_providers ALTER COLUMN id SET DEFAULT nextval('public.insurance_providers_id_seq'::regclass);


--
-- Name: maintenance_alerts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_alerts ALTER COLUMN id SET DEFAULT nextval('public.maintenance_alerts_id_seq'::regclass);


--
-- Name: maintenance_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_history ALTER COLUMN id SET DEFAULT nextval('public.maintenance_history_id_seq'::regclass);


--
-- Name: maintenance_records id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_records ALTER COLUMN id SET DEFAULT nextval('public.maintenance_records_id_seq'::regclass);


--
-- Name: maintenance_schedule id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedule ALTER COLUMN id SET DEFAULT nextval('public.maintenance_schedule_id_seq'::regclass);


--
-- Name: maintenance_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_statuses ALTER COLUMN id SET DEFAULT nextval('public.maintenance_statuses_id_seq'::regclass);


--
-- Name: maintenance_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_types ALTER COLUMN id SET DEFAULT nextval('public.maintenance_types_id_seq'::regclass);


--
-- Name: media_relations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_relations ALTER COLUMN id SET DEFAULT nextval('public.media_relations_id_seq'::regclass);


--
-- Name: media_storage id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_storage ALTER COLUMN id SET DEFAULT nextval('public.media_storage_id_seq'::regclass);


--
-- Name: media_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_types ALTER COLUMN id SET DEFAULT nextval('public.media_types_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: odometer_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odometer_history ALTER COLUMN id SET DEFAULT nextval('public.odometer_history_id_seq'::regclass);


--
-- Name: office_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_assignments ALTER COLUMN id SET DEFAULT nextval('public.office_assignments_id_seq'::regclass);


--
-- Name: office_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_history ALTER COLUMN id SET DEFAULT nextval('public.office_history_id_seq'::regclass);


--
-- Name: offices id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.offices ALTER COLUMN id SET DEFAULT nextval('public.offices_id_seq'::regclass);


--
-- Name: password_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_history ALTER COLUMN id SET DEFAULT nextval('public.password_history_id_seq'::regclass);


--
-- Name: password_resets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets ALTER COLUMN id SET DEFAULT nextval('public.password_resets_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: plant_equipment_fuel_card_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plant_equipment_fuel_card_assignments ALTER COLUMN id SET DEFAULT nextval('public.plant_equipment_fuel_card_assignments_id_seq'::regclass);


--
-- Name: rate_limits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rate_limits ALTER COLUMN id SET DEFAULT nextval('public.rate_limits_id_seq'::regclass);


--
-- Name: report_executions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_executions ALTER COLUMN id SET DEFAULT nextval('public.report_executions_id_seq'::regclass);


--
-- Name: report_parameters id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_parameters ALTER COLUMN id SET DEFAULT nextval('public.report_parameters_id_seq'::regclass);


--
-- Name: report_schedules id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_schedules ALTER COLUMN id SET DEFAULT nextval('public.report_schedules_id_seq'::regclass);


--
-- Name: report_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_types ALTER COLUMN id SET DEFAULT nextval('public.report_types_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: saved_reports id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saved_reports ALTER COLUMN id SET DEFAULT nextval('public.saved_reports_id_seq'::regclass);


--
-- Name: service_providers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_providers ALTER COLUMN id SET DEFAULT nextval('public.service_providers_id_seq'::regclass);


--
-- Name: trip_checkpoints id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_checkpoints ALTER COLUMN id SET DEFAULT nextval('public.trip_checkpoints_id_seq'::regclass);


--
-- Name: trip_expenses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_expenses ALTER COLUMN id SET DEFAULT nextval('public.trip_expenses_id_seq'::regclass);


--
-- Name: trip_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_history ALTER COLUMN id SET DEFAULT nextval('public.trip_history_id_seq'::regclass);


--
-- Name: trip_passengers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_passengers ALTER COLUMN id SET DEFAULT nextval('public.trip_passengers_id_seq'::regclass);


--
-- Name: trip_purposes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_purposes ALTER COLUMN id SET DEFAULT nextval('public.trip_purposes_id_seq'::regclass);


--
-- Name: trip_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_statuses ALTER COLUMN id SET DEFAULT nextval('public.trip_statuses_id_seq'::regclass);


--
-- Name: trip_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_types ALTER COLUMN id SET DEFAULT nextval('public.trip_types_id_seq'::regclass);


--
-- Name: trips id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips ALTER COLUMN id SET DEFAULT nextval('public.trips_id_seq'::regclass);


--
-- Name: user_activity id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_activity ALTER COLUMN id SET DEFAULT nextval('public.user_activity_id_seq'::regclass);


--
-- Name: user_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_history ALTER COLUMN id SET DEFAULT nextval('public.user_history_id_seq'::regclass);


--
-- Name: user_sessions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_sessions ALTER COLUMN id SET DEFAULT nextval('public.user_sessions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vehicle_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments ALTER COLUMN id SET DEFAULT nextval('public.vehicle_assignments_id_seq'::regclass);


--
-- Name: vehicle_fuel_card_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_fuel_card_assignments ALTER COLUMN id SET DEFAULT nextval('public.vehicle_fuel_card_assignments_id_seq'::regclass);


--
-- Name: vehicle_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_history ALTER COLUMN id SET DEFAULT nextval('public.vehicle_history_id_seq'::regclass);


--
-- Name: vehicle_insurance id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_insurance ALTER COLUMN id SET DEFAULT nextval('public.vehicle_insurance_id_seq'::regclass);


--
-- Name: vehicle_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_logs ALTER COLUMN id SET DEFAULT nextval('public.vehicle_logs_id_seq'::regclass);


--
-- Name: vehicle_maintenance id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_maintenance ALTER COLUMN id SET DEFAULT nextval('public.vehicle_maintenance_id_seq'::regclass);


--
-- Name: vehicle_permits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_permits ALTER COLUMN id SET DEFAULT nextval('public.vehicle_permits_id_seq'::regclass);


--
-- Name: vehicle_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_statuses ALTER COLUMN id SET DEFAULT nextval('public.vehicle_statuses_id_seq'::regclass);


--
-- Name: vehicle_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_types ALTER COLUMN id SET DEFAULT nextval('public.vehicle_types_id_seq'::regclass);


--
-- Name: vehicle_utilization id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_utilization ALTER COLUMN id SET DEFAULT nextval('public.vehicle_utilization_id_seq'::regclass);


--
-- Name: vehicles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles ALTER COLUMN id SET DEFAULT nextval('public.vehicles_id_seq'::regclass);


--
-- Name: access_requests access_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_requests
    ADD CONSTRAINT access_requests_pkey PRIMARY KEY (id);


--
-- Name: assignment_statuses assignment_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assignment_statuses
    ADD CONSTRAINT assignment_statuses_pkey PRIMARY KEY (id);


--
-- Name: assignment_types assignment_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assignment_types
    ADD CONSTRAINT assignment_types_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: departments departments_name_office_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_name_office_id_key UNIQUE (name, office_id);


--
-- Name: departments departments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (id);


--
-- Name: driver_assignments driver_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_assignments
    ADD CONSTRAINT driver_assignments_pkey PRIMARY KEY (id);


--
-- Name: driver_history driver_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_history
    ADD CONSTRAINT driver_history_pkey PRIMARY KEY (id);


--
-- Name: driver_interdiction_records driver_interdiction_records_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_interdiction_records
    ADD CONSTRAINT driver_interdiction_records_pkey PRIMARY KEY (id);


--
-- Name: driver_licenses driver_licenses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses
    ADD CONSTRAINT driver_licenses_pkey PRIMARY KEY (id);


--
-- Name: driver_status driver_status_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_status
    ADD CONSTRAINT driver_status_pkey PRIMARY KEY (id);


--
-- Name: driver_statuses driver_statuses_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_statuses
    ADD CONSTRAINT driver_statuses_name_key UNIQUE (name);


--
-- Name: driver_statuses driver_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_statuses
    ADD CONSTRAINT driver_statuses_pkey PRIMARY KEY (id);


--
-- Name: drivers drivers_personal_number_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_personal_number_key UNIQUE (personal_number);


--
-- Name: drivers drivers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_pkey PRIMARY KEY (id);


--
-- Name: fuel_card_history fuel_card_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_history
    ADD CONSTRAINT fuel_card_history_pkey PRIMARY KEY (id);


--
-- Name: fuel_card_providers fuel_card_providers_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_providers
    ADD CONSTRAINT fuel_card_providers_name_key UNIQUE (name);


--
-- Name: fuel_card_providers fuel_card_providers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_providers
    ADD CONSTRAINT fuel_card_providers_pkey PRIMARY KEY (id);


--
-- Name: fuel_card_statuses fuel_card_statuses_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_statuses
    ADD CONSTRAINT fuel_card_statuses_name_key UNIQUE (name);


--
-- Name: fuel_card_statuses fuel_card_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_statuses
    ADD CONSTRAINT fuel_card_statuses_pkey PRIMARY KEY (id);


--
-- Name: fuel_card_transactions fuel_card_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_transactions
    ADD CONSTRAINT fuel_card_transactions_pkey PRIMARY KEY (id);


--
-- Name: fuel_card_types fuel_card_types_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_types
    ADD CONSTRAINT fuel_card_types_name_key UNIQUE (name);


--
-- Name: fuel_card_types fuel_card_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_types
    ADD CONSTRAINT fuel_card_types_pkey PRIMARY KEY (id);


--
-- Name: fuel_cards fuel_cards_card_number_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_card_number_key UNIQUE (card_number);


--
-- Name: fuel_cards fuel_cards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_pkey PRIMARY KEY (id);


--
-- Name: fuel_records fuel_records_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_records
    ADD CONSTRAINT fuel_records_pkey PRIMARY KEY (id);


--
-- Name: fuel_stations fuel_stations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_stations
    ADD CONSTRAINT fuel_stations_pkey PRIMARY KEY (id);


--
-- Name: fuel_transaction_history fuel_transaction_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transaction_history
    ADD CONSTRAINT fuel_transaction_history_pkey PRIMARY KEY (id);


--
-- Name: fuel_transactions fuel_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transactions
    ADD CONSTRAINT fuel_transactions_pkey PRIMARY KEY (id);


--
-- Name: fuel_types fuel_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_types
    ADD CONSTRAINT fuel_types_pkey PRIMARY KEY (id);


--
-- Name: general_fuel_card_assignments general_fuel_card_assignments_department_id_fuel_card_id_as_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.general_fuel_card_assignments
    ADD CONSTRAINT general_fuel_card_assignments_department_id_fuel_card_id_as_key UNIQUE (department_id, fuel_card_id, assigned_at);


--
-- Name: general_fuel_card_assignments general_fuel_card_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.general_fuel_card_assignments
    ADD CONSTRAINT general_fuel_card_assignments_pkey PRIMARY KEY (id);


--
-- Name: incident_history incident_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_history
    ADD CONSTRAINT incident_history_pkey PRIMARY KEY (id);


--
-- Name: incident_photos incident_photos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_photos
    ADD CONSTRAINT incident_photos_pkey PRIMARY KEY (id);


--
-- Name: incident_repairs incident_repairs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_repairs
    ADD CONSTRAINT incident_repairs_pkey PRIMARY KEY (id);


--
-- Name: incident_severities incident_severities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_severities
    ADD CONSTRAINT incident_severities_pkey PRIMARY KEY (id);


--
-- Name: incident_statuses incident_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_statuses
    ADD CONSTRAINT incident_statuses_pkey PRIMARY KEY (id);


--
-- Name: incident_types incident_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_types
    ADD CONSTRAINT incident_types_pkey PRIMARY KEY (id);


--
-- Name: incident_witnesses incident_witnesses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_witnesses
    ADD CONSTRAINT incident_witnesses_pkey PRIMARY KEY (id);


--
-- Name: incidents incidents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents
    ADD CONSTRAINT incidents_pkey PRIMARY KEY (id);


--
-- Name: insurance_providers insurance_providers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insurance_providers
    ADD CONSTRAINT insurance_providers_pkey PRIMARY KEY (id);


--
-- Name: maintenance_alerts maintenance_alerts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_alerts
    ADD CONSTRAINT maintenance_alerts_pkey PRIMARY KEY (id);


--
-- Name: maintenance_history maintenance_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_history
    ADD CONSTRAINT maintenance_history_pkey PRIMARY KEY (id);


--
-- Name: maintenance_records maintenance_records_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT maintenance_records_pkey PRIMARY KEY (id);


--
-- Name: maintenance_schedule maintenance_schedule_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedule
    ADD CONSTRAINT maintenance_schedule_pkey PRIMARY KEY (id);


--
-- Name: maintenance_statuses maintenance_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_statuses
    ADD CONSTRAINT maintenance_statuses_pkey PRIMARY KEY (id);


--
-- Name: maintenance_types maintenance_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_types
    ADD CONSTRAINT maintenance_types_pkey PRIMARY KEY (id);


--
-- Name: media_relations media_relations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_relations
    ADD CONSTRAINT media_relations_pkey PRIMARY KEY (id);


--
-- Name: media_storage media_storage_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_storage
    ADD CONSTRAINT media_storage_pkey PRIMARY KEY (id);


--
-- Name: media_types media_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_types
    ADD CONSTRAINT media_types_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: odometer_history odometer_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odometer_history
    ADD CONSTRAINT odometer_history_pkey PRIMARY KEY (id);


--
-- Name: office_assignments office_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_assignments
    ADD CONSTRAINT office_assignments_pkey PRIMARY KEY (id);


--
-- Name: office_history office_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_history
    ADD CONSTRAINT office_history_pkey PRIMARY KEY (id);


--
-- Name: offices offices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.offices
    ADD CONSTRAINT offices_pkey PRIMARY KEY (id);


--
-- Name: password_history password_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_history
    ADD CONSTRAINT password_history_pkey PRIMARY KEY (id);


--
-- Name: password_resets password_resets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_pkey PRIMARY KEY (id);


--
-- Name: password_resets password_resets_token_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_token_key UNIQUE (token);


--
-- Name: permissions permissions_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_key UNIQUE (name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: plant_equipment_fuel_card_assignments plant_equipment_fuel_card_ass_equipment_name_fuel_card_id_a_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plant_equipment_fuel_card_assignments
    ADD CONSTRAINT plant_equipment_fuel_card_ass_equipment_name_fuel_card_id_a_key UNIQUE (equipment_name, fuel_card_id, assigned_at);


--
-- Name: plant_equipment_fuel_card_assignments plant_equipment_fuel_card_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plant_equipment_fuel_card_assignments
    ADD CONSTRAINT plant_equipment_fuel_card_assignments_pkey PRIMARY KEY (id);


--
-- Name: rate_limits rate_limits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rate_limits
    ADD CONSTRAINT rate_limits_pkey PRIMARY KEY (id);


--
-- Name: report_executions report_executions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_executions
    ADD CONSTRAINT report_executions_pkey PRIMARY KEY (id);


--
-- Name: report_parameters report_parameters_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_parameters
    ADD CONSTRAINT report_parameters_pkey PRIMARY KEY (id);


--
-- Name: report_parameters report_parameters_report_type_id_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_parameters
    ADD CONSTRAINT report_parameters_report_type_id_name_key UNIQUE (report_type_id, name);


--
-- Name: report_schedules report_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_pkey PRIMARY KEY (id);


--
-- Name: report_types report_types_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_types
    ADD CONSTRAINT report_types_name_key UNIQUE (name);


--
-- Name: report_types report_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_types
    ADD CONSTRAINT report_types_pkey PRIMARY KEY (id);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (role_id, permission_id);


--
-- Name: roles roles_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_key UNIQUE (name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: saved_reports saved_reports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saved_reports
    ADD CONSTRAINT saved_reports_pkey PRIMARY KEY (id);


--
-- Name: service_providers service_providers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_providers
    ADD CONSTRAINT service_providers_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: trip_checkpoints trip_checkpoints_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_checkpoints
    ADD CONSTRAINT trip_checkpoints_pkey PRIMARY KEY (id);


--
-- Name: trip_expenses trip_expenses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_expenses
    ADD CONSTRAINT trip_expenses_pkey PRIMARY KEY (id);


--
-- Name: trip_history trip_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_history
    ADD CONSTRAINT trip_history_pkey PRIMARY KEY (id);


--
-- Name: trip_passengers trip_passengers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_passengers
    ADD CONSTRAINT trip_passengers_pkey PRIMARY KEY (id);


--
-- Name: trip_purposes trip_purposes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_purposes
    ADD CONSTRAINT trip_purposes_pkey PRIMARY KEY (id);


--
-- Name: trip_statuses trip_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_statuses
    ADD CONSTRAINT trip_statuses_pkey PRIMARY KEY (id);


--
-- Name: trip_types trip_types_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_types
    ADD CONSTRAINT trip_types_name_key UNIQUE (name);


--
-- Name: trip_types trip_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_types
    ADD CONSTRAINT trip_types_pkey PRIMARY KEY (id);


--
-- Name: trips trips_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_pkey PRIMARY KEY (id);


--
-- Name: rate_limits unique_key_created; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rate_limits
    ADD CONSTRAINT unique_key_created UNIQUE (key, created_at);


--
-- Name: user_activity user_activity_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_activity
    ADD CONSTRAINT user_activity_pkey PRIMARY KEY (id);


--
-- Name: user_history user_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_history
    ADD CONSTRAINT user_history_pkey PRIMARY KEY (id);


--
-- Name: user_sessions user_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_pkey PRIMARY KEY (id);


--
-- Name: user_sessions user_sessions_token_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_token_key UNIQUE (token);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_personal_number_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_personal_number_key UNIQUE (personal_number);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vehicle_assignments vehicle_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_pkey PRIMARY KEY (id);


--
-- Name: vehicle_assignments vehicle_assignments_vehicle_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_vehicle_id_key UNIQUE (vehicle_id);


--
-- Name: vehicle_fuel_card_assignments vehicle_fuel_card_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_fuel_card_assignments
    ADD CONSTRAINT vehicle_fuel_card_assignments_pkey PRIMARY KEY (id);


--
-- Name: vehicle_fuel_card_assignments vehicle_fuel_card_assignments_vehicle_id_fuel_card_id_assig_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_fuel_card_assignments
    ADD CONSTRAINT vehicle_fuel_card_assignments_vehicle_id_fuel_card_id_assig_key UNIQUE (vehicle_id, fuel_card_id, assigned_at);


--
-- Name: vehicle_history vehicle_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_history
    ADD CONSTRAINT vehicle_history_pkey PRIMARY KEY (id);


--
-- Name: vehicle_insurance vehicle_insurance_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_insurance
    ADD CONSTRAINT vehicle_insurance_pkey PRIMARY KEY (id);


--
-- Name: vehicle_locations vehicle_locations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_locations
    ADD CONSTRAINT vehicle_locations_pkey PRIMARY KEY (vehicle_id);


--
-- Name: vehicle_logs vehicle_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_logs
    ADD CONSTRAINT vehicle_logs_pkey PRIMARY KEY (id);


--
-- Name: vehicle_maintenance vehicle_maintenance_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_maintenance
    ADD CONSTRAINT vehicle_maintenance_pkey PRIMARY KEY (id);


--
-- Name: vehicle_permits vehicle_permits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_permits
    ADD CONSTRAINT vehicle_permits_pkey PRIMARY KEY (id);


--
-- Name: vehicle_statuses vehicle_statuses_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_statuses
    ADD CONSTRAINT vehicle_statuses_name_key UNIQUE (name);


--
-- Name: vehicle_statuses vehicle_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_statuses
    ADD CONSTRAINT vehicle_statuses_pkey PRIMARY KEY (id);


--
-- Name: vehicle_types vehicle_types_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_types
    ADD CONSTRAINT vehicle_types_name_key UNIQUE (name);


--
-- Name: vehicle_types vehicle_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_types
    ADD CONSTRAINT vehicle_types_pkey PRIMARY KEY (id);


--
-- Name: vehicle_utilization vehicle_utilization_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_utilization
    ADD CONSTRAINT vehicle_utilization_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_registration_no_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_registration_no_key UNIQUE (registration_no);


--
-- Name: idx_access_requests_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_access_requests_email ON public.access_requests USING btree (email);


--
-- Name: idx_access_requests_personal_number; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_access_requests_personal_number ON public.access_requests USING btree (personal_number);


--
-- Name: idx_access_requests_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_access_requests_status ON public.access_requests USING btree (status);


--
-- Name: idx_departments_office; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_departments_office ON public.departments USING btree (office_id);


--
-- Name: idx_driver_assignments_driver_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_assignments_driver_id ON public.driver_assignments USING btree (driver_id);


--
-- Name: idx_driver_assignments_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_assignments_status ON public.driver_assignments USING btree (status);


--
-- Name: idx_driver_assignments_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_assignments_vehicle_id ON public.driver_assignments USING btree (vehicle_id);


--
-- Name: idx_driver_history_driver_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_history_driver_id ON public.driver_history USING btree (driver_id);


--
-- Name: idx_driver_interdiction_driver; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_interdiction_driver ON public.driver_interdiction_records USING btree (driver_id);


--
-- Name: idx_driver_interdiction_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_interdiction_status ON public.driver_interdiction_records USING btree (status);


--
-- Name: idx_driver_licenses_driver_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_licenses_driver_id ON public.driver_licenses USING btree (driver_id);


--
-- Name: idx_driver_licenses_license_number; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_licenses_license_number ON public.driver_licenses USING btree (license_number);


--
-- Name: idx_driver_licenses_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_licenses_status ON public.driver_licenses USING btree (status);


--
-- Name: idx_driver_status_driver; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_status_driver ON public.driver_status USING btree (driver_id);


--
-- Name: idx_driver_status_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_driver_status_type ON public.driver_status USING btree (status_type);


--
-- Name: idx_drivers_department_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_drivers_department_id ON public.drivers USING btree (department_id);


--
-- Name: idx_drivers_office_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_drivers_office_id ON public.drivers USING btree (office_id);


--
-- Name: idx_drivers_personal_number; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_drivers_personal_number ON public.drivers USING btree (personal_number);


--
-- Name: idx_drivers_status_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_drivers_status_id ON public.drivers USING btree (status_id);


--
-- Name: idx_drivers_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_drivers_user_id ON public.drivers USING btree (user_id);


--
-- Name: idx_expires; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_expires ON public.password_resets USING btree (expires_at);


--
-- Name: idx_fuel_card_history_card_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_card_history_card_id ON public.fuel_card_history USING btree (card_id);


--
-- Name: idx_fuel_cards_card_number; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_cards_card_number ON public.fuel_cards USING btree (card_number);


--
-- Name: idx_fuel_records_driver_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_records_driver_id ON public.fuel_records USING btree (driver_id);


--
-- Name: idx_fuel_records_fuel_card_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_records_fuel_card_id ON public.fuel_records USING btree (fuel_card_id);


--
-- Name: idx_fuel_records_transaction_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_records_transaction_date ON public.fuel_records USING btree (transaction_date);


--
-- Name: idx_fuel_records_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_records_vehicle_id ON public.fuel_records USING btree (vehicle_id);


--
-- Name: idx_fuel_transactions_card_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_transactions_card_id ON public.fuel_transactions USING btree (card_id);


--
-- Name: idx_fuel_transactions_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_transactions_date ON public.fuel_transactions USING btree (transaction_date);


--
-- Name: idx_fuel_transactions_trip_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_fuel_transactions_trip_id ON public.fuel_transactions USING btree (trip_id);


--
-- Name: idx_key_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_key_created ON public.rate_limits USING btree (key, created_at);


--
-- Name: idx_maintenance_alerts_alert_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_alerts_alert_type ON public.maintenance_alerts USING btree (alert_type);


--
-- Name: idx_maintenance_alerts_due_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_alerts_due_date ON public.maintenance_alerts USING btree (due_date);


--
-- Name: idx_maintenance_alerts_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_alerts_status ON public.maintenance_alerts USING btree (status);


--
-- Name: idx_maintenance_alerts_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_alerts_vehicle_id ON public.maintenance_alerts USING btree (vehicle_id);


--
-- Name: idx_maintenance_schedule_next_maintenance_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_schedule_next_maintenance_date ON public.maintenance_schedule USING btree (next_maintenance_date);


--
-- Name: idx_maintenance_schedule_scheduled_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_schedule_scheduled_date ON public.maintenance_schedule USING btree (scheduled_date);


--
-- Name: idx_maintenance_schedule_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_schedule_status ON public.maintenance_schedule USING btree (status);


--
-- Name: idx_maintenance_schedule_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_maintenance_schedule_vehicle_id ON public.maintenance_schedule USING btree (vehicle_id);


--
-- Name: idx_odometer_history_vehicle; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_odometer_history_vehicle ON public.odometer_history USING btree (vehicle_id);


--
-- Name: idx_office_assignments_office_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_office_assignments_office_id ON public.office_assignments USING btree (office_id);


--
-- Name: idx_office_assignments_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_office_assignments_status ON public.office_assignments USING btree (status);


--
-- Name: idx_office_assignments_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_office_assignments_vehicle_id ON public.office_assignments USING btree (vehicle_id);


--
-- Name: idx_office_history_office_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_office_history_office_id ON public.office_history USING btree (office_id);


--
-- Name: idx_offices_department_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_offices_department_id ON public.offices USING btree (department_id);


--
-- Name: idx_offices_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_offices_status ON public.offices USING btree (status);


--
-- Name: idx_password_history_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_password_history_created_at ON public.password_history USING btree (created_at);


--
-- Name: idx_password_history_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_password_history_user_id ON public.password_history USING btree (user_id);


--
-- Name: idx_password_resets_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_password_resets_status ON public.password_resets USING btree (status);


--
-- Name: idx_password_resets_token; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_password_resets_token ON public.password_resets USING btree (token);


--
-- Name: idx_password_resets_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_password_resets_user_id ON public.password_resets USING btree (user_id);


--
-- Name: idx_report_executions_saved_report_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_report_executions_saved_report_id ON public.report_executions USING btree (saved_report_id);


--
-- Name: idx_report_executions_schedule_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_report_executions_schedule_id ON public.report_executions USING btree (schedule_id);


--
-- Name: idx_report_executions_started_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_report_executions_started_at ON public.report_executions USING btree (started_at);


--
-- Name: idx_report_executions_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_report_executions_status ON public.report_executions USING btree (status);


--
-- Name: idx_report_schedules_next_run; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_report_schedules_next_run ON public.report_schedules USING btree (next_run);


--
-- Name: idx_report_schedules_saved_report_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_report_schedules_saved_report_id ON public.report_schedules USING btree (saved_report_id);


--
-- Name: idx_saved_reports_created_by; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_saved_reports_created_by ON public.saved_reports USING btree (created_by);


--
-- Name: idx_saved_reports_type_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_saved_reports_type_id ON public.saved_reports USING btree (report_type_id);


--
-- Name: idx_trip_checkpoints_trip_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trip_checkpoints_trip_id ON public.trip_checkpoints USING btree (trip_id);


--
-- Name: idx_trip_expenses_trip_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trip_expenses_trip_id ON public.trip_expenses USING btree (trip_id);


--
-- Name: idx_trip_history_trip_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trip_history_trip_id ON public.trip_history USING btree (trip_id);


--
-- Name: idx_trip_passengers_trip_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trip_passengers_trip_id ON public.trip_passengers USING btree (trip_id);


--
-- Name: idx_trips_driver_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trips_driver_id ON public.trips USING btree (driver_id);


--
-- Name: idx_trips_end_location; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trips_end_location ON public.trips USING gist (end_location);


--
-- Name: idx_trips_start_location; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trips_start_location ON public.trips USING gist (start_location);


--
-- Name: idx_trips_start_time; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trips_start_time ON public.trips USING btree (start_time);


--
-- Name: idx_trips_status_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trips_status_id ON public.trips USING btree (status_id);


--
-- Name: idx_trips_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_trips_vehicle_id ON public.trips USING btree (vehicle_id);


--
-- Name: idx_user_activity_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_activity_created_at ON public.user_activity USING btree (created_at);


--
-- Name: idx_user_activity_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_activity_type ON public.user_activity USING btree (activity_type);


--
-- Name: idx_user_activity_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_activity_user_id ON public.user_activity USING btree (user_id);


--
-- Name: idx_user_history_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_history_created_at ON public.user_history USING btree (created_at);


--
-- Name: idx_user_history_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_history_user_id ON public.user_history USING btree (user_id);


--
-- Name: idx_user_sessions_token; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_sessions_token ON public.user_sessions USING btree (token);


--
-- Name: idx_user_sessions_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_sessions_user_id ON public.user_sessions USING btree (user_id);


--
-- Name: idx_users_department_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_department_id ON public.users USING btree (department_id);


--
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- Name: idx_users_office_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_office_id ON public.users USING btree (office_id);


--
-- Name: idx_users_personal_number; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_personal_number ON public.users USING btree (personal_number);


--
-- Name: idx_users_role_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_role_id ON public.users USING btree (role_id);


--
-- Name: idx_users_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_status ON public.users USING btree (status);


--
-- Name: idx_vehicle_assignments_department; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_assignments_department ON public.vehicle_assignments USING btree (department_id);


--
-- Name: idx_vehicle_assignments_driver; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_assignments_driver ON public.vehicle_assignments USING btree (driver_id);


--
-- Name: idx_vehicle_assignments_office; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_assignments_office ON public.vehicle_assignments USING btree (office_id);


--
-- Name: idx_vehicle_assignments_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_assignments_user ON public.vehicle_assignments USING btree (user_id);


--
-- Name: idx_vehicle_assignments_vehicle; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_assignments_vehicle ON public.vehicle_assignments USING btree (vehicle_id);


--
-- Name: idx_vehicle_history_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_history_vehicle_id ON public.vehicle_history USING btree (vehicle_id);


--
-- Name: idx_vehicle_insurance_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_insurance_status ON public.vehicle_insurance USING btree (status);


--
-- Name: idx_vehicle_insurance_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_insurance_vehicle_id ON public.vehicle_insurance USING btree (vehicle_id);


--
-- Name: idx_vehicle_locations_coordinates; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_locations_coordinates ON public.vehicle_locations USING gist (public.st_setsrid(public.st_makepoint((longitude)::double precision, (latitude)::double precision), 4326));


--
-- Name: idx_vehicle_logs_driver_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_logs_driver_id ON public.vehicle_logs USING btree (driver_id);


--
-- Name: idx_vehicle_logs_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_logs_status ON public.vehicle_logs USING btree (status);


--
-- Name: idx_vehicle_logs_trip_start; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_logs_trip_start ON public.vehicle_logs USING btree (trip_start);


--
-- Name: idx_vehicle_logs_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_logs_vehicle_id ON public.vehicle_logs USING btree (vehicle_id);


--
-- Name: idx_vehicle_maintenance_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_maintenance_status ON public.vehicle_maintenance USING btree (status);


--
-- Name: idx_vehicle_maintenance_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_maintenance_vehicle_id ON public.vehicle_maintenance USING btree (vehicle_id);


--
-- Name: idx_vehicle_permits_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_permits_status ON public.vehicle_permits USING btree (status);


--
-- Name: idx_vehicle_permits_vehicle_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicle_permits_vehicle_id ON public.vehicle_permits USING btree (vehicle_id);


--
-- Name: idx_vehicles_department; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicles_department ON public.vehicles USING btree (department_id);


--
-- Name: idx_vehicles_make_model; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicles_make_model ON public.vehicles USING btree (make, model);


--
-- Name: idx_vehicles_office; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicles_office ON public.vehicles USING btree (office_id);


--
-- Name: idx_vehicles_registration; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicles_registration ON public.vehicles USING btree (registration_no);


--
-- Name: idx_vehicles_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vehicles_user ON public.vehicles USING btree (assigned_user_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: trips calculate_trip_distance; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER calculate_trip_distance BEFORE INSERT OR UPDATE OF start_location, end_location ON public.trips FOR EACH ROW EXECUTE FUNCTION public.update_trip_distance();


--
-- Name: users check_login_attempts; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER check_login_attempts BEFORE UPDATE OF login_attempts ON public.users FOR EACH ROW EXECUTE FUNCTION public.handle_login_attempt();


--
-- Name: users handle_successful_login; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER handle_successful_login BEFORE UPDATE OF last_login ON public.users FOR EACH ROW EXECUTE FUNCTION public.reset_login_attempts();


--
-- Name: vehicles standardize_vehicle_on_insert; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER standardize_vehicle_on_insert BEFORE INSERT ON public.vehicles FOR EACH ROW EXECUTE FUNCTION public.standardize_vehicle_data();


--
-- Name: vehicles standardize_vehicle_on_update; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER standardize_vehicle_on_update BEFORE UPDATE ON public.vehicles FOR EACH ROW EXECUTE FUNCTION public.standardize_vehicle_data();


--
-- Name: drivers track_driver_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_driver_changes AFTER DELETE OR UPDATE ON public.drivers FOR EACH ROW EXECUTE FUNCTION public.track_driver_changes();


--
-- Name: fuel_cards track_fuel_card_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_fuel_card_changes AFTER UPDATE ON public.fuel_cards FOR EACH ROW EXECUTE FUNCTION public.update_fuel_card_history();


--
-- Name: fuel_transactions track_fuel_transaction_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_fuel_transaction_changes AFTER UPDATE ON public.fuel_transactions FOR EACH ROW EXECUTE FUNCTION public.update_fuel_transaction_history();


--
-- Name: offices track_office_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_office_changes AFTER DELETE OR UPDATE ON public.offices FOR EACH ROW EXECUTE FUNCTION public.track_office_changes();


--
-- Name: trips track_trip_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_trip_changes AFTER UPDATE ON public.trips FOR EACH ROW EXECUTE FUNCTION public.update_trip_history();


--
-- Name: users track_user_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_user_changes AFTER DELETE OR UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.track_user_changes();


--
-- Name: vehicles track_vehicle_changes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER track_vehicle_changes AFTER DELETE OR UPDATE ON public.vehicles FOR EACH ROW EXECUTE FUNCTION public.track_vehicle_changes();


--
-- Name: access_requests update_access_requests_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_access_requests_updated_at BEFORE UPDATE ON public.access_requests FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: departments update_departments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_departments_updated_at BEFORE UPDATE ON public.departments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: driver_assignments update_driver_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_driver_assignments_updated_at BEFORE UPDATE ON public.driver_assignments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: driver_interdiction_records update_driver_interdiction_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_driver_interdiction_updated_at BEFORE UPDATE ON public.driver_interdiction_records FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: driver_licenses update_driver_licenses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_driver_licenses_updated_at BEFORE UPDATE ON public.driver_licenses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: driver_status update_driver_status_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_driver_status_updated_at BEFORE UPDATE ON public.driver_status FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: driver_statuses update_driver_statuses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_driver_statuses_updated_at BEFORE UPDATE ON public.driver_statuses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: drivers update_drivers_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_drivers_updated_at BEFORE UPDATE ON public.drivers FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: fuel_card_providers update_fuel_card_providers_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_fuel_card_providers_updated_at BEFORE UPDATE ON public.fuel_card_providers FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: fuel_card_types update_fuel_card_types_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_fuel_card_types_updated_at BEFORE UPDATE ON public.fuel_card_types FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: fuel_cards update_fuel_cards_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_fuel_cards_updated_at BEFORE UPDATE ON public.fuel_cards FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: fuel_stations update_fuel_stations_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_fuel_stations_updated_at BEFORE UPDATE ON public.fuel_stations FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: fuel_transactions update_fuel_transactions_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_fuel_transactions_updated_at BEFORE UPDATE ON public.fuel_transactions FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: general_fuel_card_assignments update_general_fuel_card_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_general_fuel_card_assignments_updated_at BEFORE UPDATE ON public.general_fuel_card_assignments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: incident_repairs update_incident_repairs_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_incident_repairs_updated_at BEFORE UPDATE ON public.incident_repairs FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: incident_severities update_incident_severities_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_incident_severities_updated_at BEFORE UPDATE ON public.incident_severities FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: incident_statuses update_incident_statuses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_incident_statuses_updated_at BEFORE UPDATE ON public.incident_statuses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: incident_types update_incident_types_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_incident_types_updated_at BEFORE UPDATE ON public.incident_types FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: incident_witnesses update_incident_witnesses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_incident_witnesses_updated_at BEFORE UPDATE ON public.incident_witnesses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: incidents update_incidents_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_incidents_updated_at BEFORE UPDATE ON public.incidents FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: insurance_providers update_insurance_providers_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_insurance_providers_updated_at BEFORE UPDATE ON public.insurance_providers FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: maintenance_records update_maintenance_records_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_maintenance_records_updated_at BEFORE UPDATE ON public.maintenance_records FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: maintenance_statuses update_maintenance_statuses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_maintenance_statuses_updated_at BEFORE UPDATE ON public.maintenance_statuses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: maintenance_types update_maintenance_types_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_maintenance_types_updated_at BEFORE UPDATE ON public.maintenance_types FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: media_storage update_media_storage_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_media_storage_updated_at BEFORE UPDATE ON public.media_storage FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: media_types update_media_types_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_media_types_updated_at BEFORE UPDATE ON public.media_types FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: odometer_history update_odometer_history_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_odometer_history_updated_at BEFORE UPDATE ON public.odometer_history FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: office_assignments update_office_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_office_assignments_updated_at BEFORE UPDATE ON public.office_assignments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: offices update_offices_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_offices_updated_at BEFORE UPDATE ON public.offices FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: password_resets update_password_resets_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_password_resets_updated_at BEFORE UPDATE ON public.password_resets FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: permissions update_permissions_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_permissions_updated_at BEFORE UPDATE ON public.permissions FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: plant_equipment_fuel_card_assignments update_plant_equipment_fuel_card_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_plant_equipment_fuel_card_assignments_updated_at BEFORE UPDATE ON public.plant_equipment_fuel_card_assignments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: report_parameters update_report_parameters_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_report_parameters_updated_at BEFORE UPDATE ON public.report_parameters FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: report_schedules update_report_schedules_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_report_schedules_updated_at BEFORE UPDATE ON public.report_schedules FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: report_types update_report_types_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_report_types_updated_at BEFORE UPDATE ON public.report_types FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: roles update_roles_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_roles_updated_at BEFORE UPDATE ON public.roles FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: saved_reports update_saved_reports_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_saved_reports_updated_at BEFORE UPDATE ON public.saved_reports FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trip_checkpoints update_trip_checkpoints_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trip_checkpoints_updated_at BEFORE UPDATE ON public.trip_checkpoints FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trip_expenses update_trip_expenses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trip_expenses_updated_at BEFORE UPDATE ON public.trip_expenses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trip_passengers update_trip_passengers_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trip_passengers_updated_at BEFORE UPDATE ON public.trip_passengers FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trip_purposes update_trip_purposes_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trip_purposes_updated_at BEFORE UPDATE ON public.trip_purposes FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trip_statuses update_trip_statuses_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trip_statuses_updated_at BEFORE UPDATE ON public.trip_statuses FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trip_types update_trip_types_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trip_types_updated_at BEFORE UPDATE ON public.trip_types FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: trips update_trips_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_trips_updated_at BEFORE UPDATE ON public.trips FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: user_sessions update_user_sessions_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_user_sessions_updated_at BEFORE UPDATE ON public.user_sessions FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: users update_users_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: vehicle_assignments update_vehicle_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_vehicle_assignments_updated_at BEFORE UPDATE ON public.vehicle_assignments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: vehicle_fuel_card_assignments update_vehicle_fuel_card_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_vehicle_fuel_card_assignments_updated_at BEFORE UPDATE ON public.vehicle_fuel_card_assignments FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: vehicle_locations update_vehicle_locations_last_updated; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_vehicle_locations_last_updated BEFORE UPDATE ON public.vehicle_locations FOR EACH ROW EXECUTE FUNCTION public.update_last_updated_column();


--
-- Name: vehicles update_vehicles_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_vehicles_updated_at BEFORE UPDATE ON public.vehicles FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: access_requests access_requests_processed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_requests
    ADD CONSTRAINT access_requests_processed_by_fkey FOREIGN KEY (processed_by) REFERENCES public.users(id);


--
-- Name: access_requests access_requests_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_requests
    ADD CONSTRAINT access_requests_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id);


--
-- Name: departments departments_office_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_office_id_fkey FOREIGN KEY (office_id) REFERENCES public.offices(id);


--
-- Name: driver_assignments driver_assignments_assigned_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_assignments
    ADD CONSTRAINT driver_assignments_assigned_by_fkey FOREIGN KEY (assigned_by) REFERENCES public.users(id);


--
-- Name: driver_assignments driver_assignments_driver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_assignments
    ADD CONSTRAINT driver_assignments_driver_id_fkey FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: driver_assignments driver_assignments_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_assignments
    ADD CONSTRAINT driver_assignments_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: driver_history driver_history_changed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_history
    ADD CONSTRAINT driver_history_changed_by_fkey FOREIGN KEY (changed_by) REFERENCES public.users(id);


--
-- Name: driver_history driver_history_driver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_history
    ADD CONSTRAINT driver_history_driver_id_fkey FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: driver_licenses driver_licenses_driver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses
    ADD CONSTRAINT driver_licenses_driver_id_fkey FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: drivers drivers_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.driver_statuses(id);


--
-- Name: drivers drivers_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: fuel_card_history fuel_card_history_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_history
    ADD CONSTRAINT fuel_card_history_card_id_fkey FOREIGN KEY (card_id) REFERENCES public.fuel_cards(id) ON DELETE CASCADE;


--
-- Name: fuel_card_history fuel_card_history_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_history
    ADD CONSTRAINT fuel_card_history_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: fuel_card_history fuel_card_history_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_history
    ADD CONSTRAINT fuel_card_history_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.fuel_card_statuses(id);


--
-- Name: fuel_card_transactions fuel_card_transactions_fuel_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_card_transactions
    ADD CONSTRAINT fuel_card_transactions_fuel_card_id_fkey FOREIGN KEY (fuel_card_id) REFERENCES public.fuel_cards(id);


--
-- Name: fuel_cards fuel_cards_assigned_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_assigned_vehicle_id_fkey FOREIGN KEY (assigned_vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: fuel_cards fuel_cards_fuel_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_fuel_type_id_fkey FOREIGN KEY (fuel_type_id) REFERENCES public.fuel_types(id);


--
-- Name: fuel_cards fuel_cards_service_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_service_provider_id_fkey FOREIGN KEY (service_provider_id) REFERENCES public.service_providers(id);


--
-- Name: fuel_records fuel_records_driver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_records
    ADD CONSTRAINT fuel_records_driver_id_fkey FOREIGN KEY (driver_id) REFERENCES public.users(id);


--
-- Name: fuel_records fuel_records_fuel_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_records
    ADD CONSTRAINT fuel_records_fuel_card_id_fkey FOREIGN KEY (fuel_card_id) REFERENCES public.fuel_cards(id);


--
-- Name: fuel_records fuel_records_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_records
    ADD CONSTRAINT fuel_records_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: fuel_transaction_history fuel_transaction_history_transaction_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transaction_history
    ADD CONSTRAINT fuel_transaction_history_transaction_id_fkey FOREIGN KEY (transaction_id) REFERENCES public.fuel_transactions(id);


--
-- Name: fuel_transactions fuel_transactions_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transactions
    ADD CONSTRAINT fuel_transactions_card_id_fkey FOREIGN KEY (card_id) REFERENCES public.fuel_cards(id);


--
-- Name: fuel_transactions fuel_transactions_station_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transactions
    ADD CONSTRAINT fuel_transactions_station_id_fkey FOREIGN KEY (station_id) REFERENCES public.fuel_stations(id);


--
-- Name: fuel_transactions fuel_transactions_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transactions
    ADD CONSTRAINT fuel_transactions_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id);


--
-- Name: fuel_transactions fuel_transactions_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_transactions
    ADD CONSTRAINT fuel_transactions_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: general_fuel_card_assignments general_fuel_card_assignments_department_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.general_fuel_card_assignments
    ADD CONSTRAINT general_fuel_card_assignments_department_id_fkey FOREIGN KEY (department_id) REFERENCES public.departments(id);


--
-- Name: general_fuel_card_assignments general_fuel_card_assignments_fuel_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.general_fuel_card_assignments
    ADD CONSTRAINT general_fuel_card_assignments_fuel_card_id_fkey FOREIGN KEY (fuel_card_id) REFERENCES public.fuel_cards(id);


--
-- Name: incident_history incident_history_incident_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_history
    ADD CONSTRAINT incident_history_incident_id_fkey FOREIGN KEY (incident_id) REFERENCES public.incidents(id);


--
-- Name: incident_photos incident_photos_incident_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_photos
    ADD CONSTRAINT incident_photos_incident_id_fkey FOREIGN KEY (incident_id) REFERENCES public.incidents(id);


--
-- Name: incident_repairs incident_repairs_incident_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_repairs
    ADD CONSTRAINT incident_repairs_incident_id_fkey FOREIGN KEY (incident_id) REFERENCES public.incidents(id);


--
-- Name: incident_repairs incident_repairs_service_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_repairs
    ADD CONSTRAINT incident_repairs_service_provider_id_fkey FOREIGN KEY (service_provider_id) REFERENCES public.service_providers(id);


--
-- Name: incident_witnesses incident_witnesses_incident_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incident_witnesses
    ADD CONSTRAINT incident_witnesses_incident_id_fkey FOREIGN KEY (incident_id) REFERENCES public.incidents(id);


--
-- Name: incidents incidents_incident_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents
    ADD CONSTRAINT incidents_incident_type_id_fkey FOREIGN KEY (incident_type_id) REFERENCES public.incident_types(id);


--
-- Name: incidents incidents_insurance_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents
    ADD CONSTRAINT incidents_insurance_provider_id_fkey FOREIGN KEY (insurance_provider_id) REFERENCES public.insurance_providers(id);


--
-- Name: incidents incidents_severity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents
    ADD CONSTRAINT incidents_severity_id_fkey FOREIGN KEY (severity_id) REFERENCES public.incident_severities(id);


--
-- Name: incidents incidents_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents
    ADD CONSTRAINT incidents_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.incident_statuses(id);


--
-- Name: incidents incidents_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidents
    ADD CONSTRAINT incidents_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: maintenance_alerts maintenance_alerts_resolved_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_alerts
    ADD CONSTRAINT maintenance_alerts_resolved_by_fkey FOREIGN KEY (resolved_by) REFERENCES public.users(id);


--
-- Name: maintenance_alerts maintenance_alerts_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_alerts
    ADD CONSTRAINT maintenance_alerts_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: maintenance_history maintenance_history_maintenance_record_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_history
    ADD CONSTRAINT maintenance_history_maintenance_record_id_fkey FOREIGN KEY (maintenance_record_id) REFERENCES public.maintenance_records(id);


--
-- Name: maintenance_records maintenance_records_maintenance_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT maintenance_records_maintenance_type_id_fkey FOREIGN KEY (maintenance_type_id) REFERENCES public.maintenance_types(id);


--
-- Name: maintenance_records maintenance_records_service_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT maintenance_records_service_provider_id_fkey FOREIGN KEY (service_provider_id) REFERENCES public.service_providers(id);


--
-- Name: maintenance_records maintenance_records_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT maintenance_records_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.maintenance_statuses(id);


--
-- Name: maintenance_records maintenance_records_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT maintenance_records_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: maintenance_schedule maintenance_schedule_completed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedule
    ADD CONSTRAINT maintenance_schedule_completed_by_fkey FOREIGN KEY (completed_by) REFERENCES public.users(id);


--
-- Name: maintenance_schedule maintenance_schedule_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedule
    ADD CONSTRAINT maintenance_schedule_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: media_relations media_relations_media_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_relations
    ADD CONSTRAINT media_relations_media_id_fkey FOREIGN KEY (media_id) REFERENCES public.media_storage(id);


--
-- Name: media_storage media_storage_media_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media_storage
    ADD CONSTRAINT media_storage_media_type_id_fkey FOREIGN KEY (media_type_id) REFERENCES public.media_types(id);


--
-- Name: odometer_history odometer_history_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odometer_history
    ADD CONSTRAINT odometer_history_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: office_assignments office_assignments_office_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_assignments
    ADD CONSTRAINT office_assignments_office_id_fkey FOREIGN KEY (office_id) REFERENCES public.offices(id);


--
-- Name: office_assignments office_assignments_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_assignments
    ADD CONSTRAINT office_assignments_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: office_history office_history_office_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.office_history
    ADD CONSTRAINT office_history_office_id_fkey FOREIGN KEY (office_id) REFERENCES public.offices(id);


--
-- Name: password_history password_history_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_history
    ADD CONSTRAINT password_history_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: password_resets password_resets_processed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_processed_by_fkey FOREIGN KEY (processed_by) REFERENCES public.users(id);


--
-- Name: password_resets password_resets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: plant_equipment_fuel_card_assignments plant_equipment_fuel_card_assignments_fuel_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plant_equipment_fuel_card_assignments
    ADD CONSTRAINT plant_equipment_fuel_card_assignments_fuel_card_id_fkey FOREIGN KEY (fuel_card_id) REFERENCES public.fuel_cards(id);


--
-- Name: report_executions report_executions_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_executions
    ADD CONSTRAINT report_executions_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: report_executions report_executions_saved_report_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_executions
    ADD CONSTRAINT report_executions_saved_report_id_fkey FOREIGN KEY (saved_report_id) REFERENCES public.saved_reports(id) ON DELETE CASCADE;


--
-- Name: report_executions report_executions_schedule_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_executions
    ADD CONSTRAINT report_executions_schedule_id_fkey FOREIGN KEY (schedule_id) REFERENCES public.report_schedules(id);


--
-- Name: report_parameters report_parameters_report_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_parameters
    ADD CONSTRAINT report_parameters_report_type_id_fkey FOREIGN KEY (report_type_id) REFERENCES public.report_types(id) ON DELETE CASCADE;


--
-- Name: report_schedules report_schedules_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: report_schedules report_schedules_saved_report_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_saved_report_id_fkey FOREIGN KEY (saved_report_id) REFERENCES public.saved_reports(id) ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_permission_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_permission_id_fkey FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: saved_reports saved_reports_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saved_reports
    ADD CONSTRAINT saved_reports_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: saved_reports saved_reports_report_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saved_reports
    ADD CONSTRAINT saved_reports_report_type_id_fkey FOREIGN KEY (report_type_id) REFERENCES public.report_types(id);


--
-- Name: trip_checkpoints trip_checkpoints_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_checkpoints
    ADD CONSTRAINT trip_checkpoints_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: trip_expenses trip_expenses_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_expenses
    ADD CONSTRAINT trip_expenses_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: trip_history trip_history_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_history
    ADD CONSTRAINT trip_history_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id);


--
-- Name: trip_passengers trip_passengers_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip_passengers
    ADD CONSTRAINT trip_passengers_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: trips trips_purpose_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_purpose_id_fkey FOREIGN KEY (purpose_id) REFERENCES public.trip_purposes(id);


--
-- Name: trips trips_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.trip_statuses(id);


--
-- Name: trips trips_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: user_activity user_activity_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_activity
    ADD CONSTRAINT user_activity_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_history user_history_changed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_history
    ADD CONSTRAINT user_history_changed_by_fkey FOREIGN KEY (changed_by) REFERENCES public.users(id);


--
-- Name: user_history user_history_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_history
    ADD CONSTRAINT user_history_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: user_sessions user_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: users users_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id);


--
-- Name: vehicle_assignments vehicle_assignments_assignment_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_assignment_type_id_fkey FOREIGN KEY (assignment_type_id) REFERENCES public.assignment_types(id);


--
-- Name: vehicle_assignments vehicle_assignments_department_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_department_id_fkey FOREIGN KEY (department_id) REFERENCES public.departments(id);


--
-- Name: vehicle_assignments vehicle_assignments_office_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_office_id_fkey FOREIGN KEY (office_id) REFERENCES public.offices(id);


--
-- Name: vehicle_assignments vehicle_assignments_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.assignment_statuses(id);


--
-- Name: vehicle_assignments vehicle_assignments_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_assignments
    ADD CONSTRAINT vehicle_assignments_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: vehicle_fuel_card_assignments vehicle_fuel_card_assignments_fuel_card_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_fuel_card_assignments
    ADD CONSTRAINT vehicle_fuel_card_assignments_fuel_card_id_fkey FOREIGN KEY (fuel_card_id) REFERENCES public.fuel_cards(id);


--
-- Name: vehicle_fuel_card_assignments vehicle_fuel_card_assignments_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_fuel_card_assignments
    ADD CONSTRAINT vehicle_fuel_card_assignments_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: vehicle_history vehicle_history_changed_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_history
    ADD CONSTRAINT vehicle_history_changed_by_fkey FOREIGN KEY (changed_by) REFERENCES public.users(id);


--
-- Name: vehicle_history vehicle_history_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_history
    ADD CONSTRAINT vehicle_history_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicle_insurance vehicle_insurance_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_insurance
    ADD CONSTRAINT vehicle_insurance_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicle_locations vehicle_locations_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_locations
    ADD CONSTRAINT vehicle_locations_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: vehicle_logs vehicle_logs_driver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_logs
    ADD CONSTRAINT vehicle_logs_driver_id_fkey FOREIGN KEY (driver_id) REFERENCES public.users(id);


--
-- Name: vehicle_logs vehicle_logs_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_logs
    ADD CONSTRAINT vehicle_logs_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: vehicle_maintenance vehicle_maintenance_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_maintenance
    ADD CONSTRAINT vehicle_maintenance_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicle_permits vehicle_permits_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_permits
    ADD CONSTRAINT vehicle_permits_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicle_utilization vehicle_utilization_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_utilization
    ADD CONSTRAINT vehicle_utilization_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id);


--
-- Name: vehicles vehicles_department_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_department_id_fkey FOREIGN KEY (department_id) REFERENCES public.departments(id);


--
-- Name: vehicles vehicles_office_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_office_id_fkey FOREIGN KEY (office_id) REFERENCES public.offices(id);


--
-- PostgreSQL database dump complete
--

