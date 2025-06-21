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
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: driver_licenses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_licenses (
    id bigint NOT NULL,
    driver_id bigint NOT NULL,
    license_number character varying(255) NOT NULL,
    issue_date date NOT NULL,
    expiry_date date NOT NULL,
    status character varying(255) DEFAULT 'Valid'::character varying NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.driver_licenses OWNER TO postgres;

--
-- Name: driver_licenses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_licenses_id_seq
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
-- Name: driver_ratings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_ratings (
    id bigint NOT NULL,
    driver_id bigint NOT NULL,
    trip_id bigint NOT NULL,
    rating numeric(3,1) NOT NULL,
    safety_score numeric(3,1) NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.driver_ratings OWNER TO postgres;

--
-- Name: driver_ratings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_ratings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_ratings_id_seq OWNER TO postgres;

--
-- Name: driver_ratings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.driver_ratings_id_seq OWNED BY public.driver_ratings.id;


--
-- Name: driver_statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.driver_statuses (
    id bigint NOT NULL,
    driver_id bigint NOT NULL,
    status character varying(255) NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.driver_statuses OWNER TO postgres;

--
-- Name: driver_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.driver_statuses_id_seq
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
    id bigint NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    license_number character varying(255),
    date_of_birth date,
    status character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.drivers OWNER TO postgres;

--
-- Name: drivers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.drivers_id_seq
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
-- Name: financial_transactions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.financial_transactions (
    id bigint NOT NULL,
    vehicle_id bigint NOT NULL,
    transaction_type character varying(50) NOT NULL,
    amount numeric(15,2) NOT NULL,
    transaction_date date NOT NULL,
    description text,
    reference_number character varying(100),
    category character varying(50),
    created_by bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.financial_transactions OWNER TO postgres;

--
-- Name: financial_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.financial_transactions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.financial_transactions_id_seq OWNER TO postgres;

--
-- Name: financial_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.financial_transactions_id_seq OWNED BY public.financial_transactions.id;


--
-- Name: fuel_cards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_cards (
    id bigint NOT NULL,
    card_number character varying(255) NOT NULL,
    provider character varying(255),
    issue_date date,
    expiry_date date,
    status character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.fuel_cards OWNER TO postgres;

--
-- Name: fuel_cards_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_cards_id_seq
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
-- Name: maintenance_schedules; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maintenance_schedules (
    id bigint NOT NULL,
    vehicle_id bigint NOT NULL,
    maintenance_type character varying(255) NOT NULL,
    scheduled_date timestamp(0) without time zone NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    description text,
    estimated_cost numeric(10,2),
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.maintenance_schedules OWNER TO postgres;

--
-- Name: maintenance_schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maintenance_schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_schedules_id_seq OWNER TO postgres;

--
-- Name: maintenance_schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.maintenance_schedules_id_seq OWNED BY public.maintenance_schedules.id;


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
-- Name: statuses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.statuses (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.statuses OWNER TO postgres;

--
-- Name: statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.statuses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.statuses_id_seq OWNER TO postgres;

--
-- Name: statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.statuses_id_seq OWNED BY public.statuses.id;


--
-- Name: trips; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trips (
    id bigint NOT NULL,
    vehicle_id bigint NOT NULL,
    driver_id bigint NOT NULL,
    start_time timestamp(0) without time zone,
    end_time timestamp(0) without time zone,
    distance numeric(10,2),
    status_id bigint,
    fuel_used numeric(10,2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.trips OWNER TO postgres;

--
-- Name: trips_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trips_id_seq
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
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    personal_number character varying(255) NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
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
-- Name: vehicle_depreciation_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_depreciation_history (
    id bigint NOT NULL,
    vehicle_id bigint NOT NULL,
    calculation_date date NOT NULL,
    purchase_price numeric(15,2) NOT NULL,
    depreciation_rate numeric(5,2) NOT NULL,
    annual_depreciation numeric(15,2) NOT NULL,
    accumulated_depreciation numeric(15,2) NOT NULL,
    net_book_value numeric(15,2) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.vehicle_depreciation_history OWNER TO postgres;

--
-- Name: vehicle_depreciation_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_depreciation_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_depreciation_history_id_seq OWNER TO postgres;

--
-- Name: vehicle_depreciation_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_depreciation_history_id_seq OWNED BY public.vehicle_depreciation_history.id;


--
-- Name: vehicle_operating_costs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_operating_costs (
    id bigint NOT NULL,
    vehicle_id bigint NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    fuel_costs numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    maintenance_costs numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    trip_expenses numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    insurance_costs numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_costs numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    revenue numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    profit_loss numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.vehicle_operating_costs OWNER TO postgres;

--
-- Name: vehicle_operating_costs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_operating_costs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_operating_costs_id_seq OWNER TO postgres;

--
-- Name: vehicle_operating_costs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_operating_costs_id_seq OWNED BY public.vehicle_operating_costs.id;


--
-- Name: vehicles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicles (
    id bigint NOT NULL,
    registration_no character varying(255) NOT NULL,
    make character varying(255),
    model character varying(255),
    purchase_date date,
    purchase_price numeric(15,2),
    depreciation_rate numeric(5,2),
    annual_depreciation numeric(15,2),
    accumulated_depreciation numeric(15,2),
    net_book_value numeric(15,2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    disposal_date date,
    disposal_value numeric(15,2),
    asset_condition character varying(50)
);


ALTER TABLE public.vehicles OWNER TO postgres;

--
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicles_id_seq
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
-- Name: driver_licenses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses ALTER COLUMN id SET DEFAULT nextval('public.driver_licenses_id_seq'::regclass);


--
-- Name: driver_ratings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_ratings ALTER COLUMN id SET DEFAULT nextval('public.driver_ratings_id_seq'::regclass);


--
-- Name: driver_statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_statuses ALTER COLUMN id SET DEFAULT nextval('public.driver_statuses_id_seq'::regclass);


--
-- Name: drivers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers ALTER COLUMN id SET DEFAULT nextval('public.drivers_id_seq'::regclass);


--
-- Name: financial_transactions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financial_transactions ALTER COLUMN id SET DEFAULT nextval('public.financial_transactions_id_seq'::regclass);


--
-- Name: fuel_cards id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards ALTER COLUMN id SET DEFAULT nextval('public.fuel_cards_id_seq'::regclass);


--
-- Name: maintenance_schedules id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedules ALTER COLUMN id SET DEFAULT nextval('public.maintenance_schedules_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: statuses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statuses ALTER COLUMN id SET DEFAULT nextval('public.statuses_id_seq'::regclass);


--
-- Name: trips id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips ALTER COLUMN id SET DEFAULT nextval('public.trips_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vehicle_depreciation_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_depreciation_history ALTER COLUMN id SET DEFAULT nextval('public.vehicle_depreciation_history_id_seq'::regclass);


--
-- Name: vehicle_operating_costs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_operating_costs ALTER COLUMN id SET DEFAULT nextval('public.vehicle_operating_costs_id_seq'::regclass);


--
-- Name: vehicles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles ALTER COLUMN id SET DEFAULT nextval('public.vehicles_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel_cache_admin001|127.0.0.1:timer	i:1750410528;	1750410528
laravel_cache_admin001|127.0.0.1	i:1;	1750410528
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: driver_licenses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.driver_licenses (id, driver_id, license_number, issue_date, expiry_date, status, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: driver_ratings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.driver_ratings (id, driver_id, trip_id, rating, safety_score, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: driver_statuses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.driver_statuses (id, driver_id, status, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: drivers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.drivers (id, first_name, last_name, license_number, date_of_birth, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: financial_transactions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.financial_transactions (id, vehicle_id, transaction_type, amount, transaction_date, description, reference_number, category, created_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: fuel_cards; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fuel_cards (id, card_number, provider, issue_date, expiry_date, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: maintenance_schedules; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.maintenance_schedules (id, vehicle_id, maintenance_type, scheduled_date, status, description, estimated_cost, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2024_03_13_000000_create_users_table	1
2	2024_03_14_000000_create_statuses_table	1
3	2024_03_15_000000_create_drivers_table	1
4	2024_03_15_000000_create_fuel_cards_table	1
5	2024_03_16_000000_create_vehicles_table	1
6	2024_03_17_000000_create_trips_table	1
7	2024_03_19_000000_create_maintenance_schedules_table	1
8	2024_03_20_000001_create_driver_licenses_table	1
9	2024_03_20_000002_create_driver_statuses_table	1
10	2024_03_20_000003_create_driver_ratings_table	1
11	2024_03_21_000000_add_financial_tracking_tables	1
12	2025_06_20_083639_create_sessions_table	2
13	2025_06_20_083744_create_cache_table	3
14	2025_06_20_090000_add_personal_number_to_users_table	4
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
JAGN5cbu1Bb4Trkoven9yvxedjh3zv9EUvQjuVrN	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:139.0) Gecko/20100101 Firefox/139.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmRrRjE5UFNkVTJYUWVlblVjS0NjUXcwMklqbTBOR2RiNWpyRVEwdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1750410469
zY90fpWMnjcysE3QbLRvQJxyvf87dmGgAmdkfjdZ	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:139.0) Gecko/20100101 Firefox/139.0	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiald5YzczdjlremFtWUdncXY4Q3M5M2pTZ0diSlpuVVlDanBNWE9LUSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1750410066
\.


--
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.spatial_ref_sys (srid, auth_name, auth_srid, srtext, proj4text) FROM stdin;
\.


--
-- Data for Name: statuses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.statuses (id, name, description, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: trips; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.trips (id, vehicle_id, driver_id, start_time, end_time, distance, status_id, fuel_used, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, personal_number) FROM stdin;
1	Admin	admin@example.com	\N	$2y$12$csNeTeUmoddiwR8QZfpjze27ROdKD7ZvdJThZRQeZH4gLnI4JRwmC	\N	2025-06-20 08:44:33	2025-06-20 08:44:33	ADMIN001
\.


--
-- Data for Name: vehicle_depreciation_history; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vehicle_depreciation_history (id, vehicle_id, calculation_date, purchase_price, depreciation_rate, annual_depreciation, accumulated_depreciation, net_book_value, created_at) FROM stdin;
\.


--
-- Data for Name: vehicle_operating_costs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vehicle_operating_costs (id, vehicle_id, period_start, period_end, fuel_costs, maintenance_costs, trip_expenses, insurance_costs, total_costs, revenue, profit_loss, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: vehicles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vehicles (id, registration_no, make, model, purchase_date, purchase_price, depreciation_rate, annual_depreciation, accumulated_depreciation, net_book_value, created_at, updated_at, disposal_date, disposal_value, asset_condition) FROM stdin;
\.


--
-- Name: driver_licenses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.driver_licenses_id_seq', 1, false);


--
-- Name: driver_ratings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.driver_ratings_id_seq', 1, false);


--
-- Name: driver_statuses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.driver_statuses_id_seq', 1, false);


--
-- Name: drivers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.drivers_id_seq', 1, false);


--
-- Name: financial_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.financial_transactions_id_seq', 1, false);


--
-- Name: fuel_cards_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fuel_cards_id_seq', 1, false);


--
-- Name: maintenance_schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.maintenance_schedules_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 14, true);


--
-- Name: statuses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.statuses_id_seq', 1, false);


--
-- Name: trips_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.trips_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: vehicle_depreciation_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vehicle_depreciation_history_id_seq', 1, false);


--
-- Name: vehicle_operating_costs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vehicle_operating_costs_id_seq', 1, false);


--
-- Name: vehicles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vehicles_id_seq', 1, false);


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
-- Name: driver_licenses driver_licenses_license_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses
    ADD CONSTRAINT driver_licenses_license_number_unique UNIQUE (license_number);


--
-- Name: driver_licenses driver_licenses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses
    ADD CONSTRAINT driver_licenses_pkey PRIMARY KEY (id);


--
-- Name: driver_ratings driver_ratings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_ratings
    ADD CONSTRAINT driver_ratings_pkey PRIMARY KEY (id);


--
-- Name: driver_statuses driver_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_statuses
    ADD CONSTRAINT driver_statuses_pkey PRIMARY KEY (id);


--
-- Name: drivers drivers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_pkey PRIMARY KEY (id);


--
-- Name: financial_transactions financial_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financial_transactions
    ADD CONSTRAINT financial_transactions_pkey PRIMARY KEY (id);


--
-- Name: fuel_cards fuel_cards_card_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_card_number_unique UNIQUE (card_number);


--
-- Name: fuel_cards fuel_cards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_cards
    ADD CONSTRAINT fuel_cards_pkey PRIMARY KEY (id);


--
-- Name: maintenance_schedules maintenance_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: statuses statuses_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statuses
    ADD CONSTRAINT statuses_name_unique UNIQUE (name);


--
-- Name: statuses statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statuses
    ADD CONSTRAINT statuses_pkey PRIMARY KEY (id);


--
-- Name: trips trips_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_personal_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_personal_number_unique UNIQUE (personal_number);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vehicle_depreciation_history vehicle_depreciation_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_depreciation_history
    ADD CONSTRAINT vehicle_depreciation_history_pkey PRIMARY KEY (id);


--
-- Name: vehicle_operating_costs vehicle_operating_costs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_operating_costs
    ADD CONSTRAINT vehicle_operating_costs_pkey PRIMARY KEY (id);


--
-- Name: vehicle_operating_costs vehicle_operating_costs_vehicle_id_period_start_period_end_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_operating_costs
    ADD CONSTRAINT vehicle_operating_costs_vehicle_id_period_start_period_end_uniq UNIQUE (vehicle_id, period_start, period_end);


--
-- Name: vehicles vehicles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_registration_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_registration_no_unique UNIQUE (registration_no);


--
-- Name: financial_transactions_transaction_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX financial_transactions_transaction_type_index ON public.financial_transactions USING btree (transaction_type);


--
-- Name: financial_transactions_vehicle_id_transaction_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX financial_transactions_vehicle_id_transaction_date_index ON public.financial_transactions USING btree (vehicle_id, transaction_date);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: vehicle_depreciation_history_vehicle_id_calculation_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX vehicle_depreciation_history_vehicle_id_calculation_date_index ON public.vehicle_depreciation_history USING btree (vehicle_id, calculation_date);


--
-- Name: vehicle_operating_costs_period_start_period_end_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX vehicle_operating_costs_period_start_period_end_index ON public.vehicle_operating_costs USING btree (period_start, period_end);


--
-- Name: driver_licenses driver_licenses_driver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_licenses
    ADD CONSTRAINT driver_licenses_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: driver_ratings driver_ratings_driver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_ratings
    ADD CONSTRAINT driver_ratings_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: driver_ratings driver_ratings_trip_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_ratings
    ADD CONSTRAINT driver_ratings_trip_id_foreign FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: driver_statuses driver_statuses_driver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.driver_statuses
    ADD CONSTRAINT driver_statuses_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: financial_transactions financial_transactions_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financial_transactions
    ADD CONSTRAINT financial_transactions_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: financial_transactions financial_transactions_vehicle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financial_transactions
    ADD CONSTRAINT financial_transactions_vehicle_id_foreign FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: maintenance_schedules maintenance_schedules_vehicle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_vehicle_id_foreign FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: trips trips_driver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- Name: trips trips_vehicle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_vehicle_id_foreign FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicle_depreciation_history vehicle_depreciation_history_vehicle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_depreciation_history
    ADD CONSTRAINT vehicle_depreciation_history_vehicle_id_foreign FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicle_operating_costs vehicle_operating_costs_vehicle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_operating_costs
    ADD CONSTRAINT vehicle_operating_costs_vehicle_id_foreign FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

