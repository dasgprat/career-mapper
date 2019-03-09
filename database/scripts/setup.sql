CREATE TABLE IF NOT EXISTS cm_company (
    cid INT NOT NULL AUTO_INCREMENT,
    c_name VARCHAR(255) NOT NULL,

    PRIMARY KEY (cid)
);

CREATE TABLE IF NOT EXISTS cm_profession (
    pid INT NOT NULL AUTO_INCREMENT,
    p_name VARCHAR(255) NOT NULL,

    PRIMARY KEY (pid)
);

CREATE TABLE IF NOT EXISTS cm_location (
    lid INT NOT NULL AUTO_INCREMENT,
    l_city VARCHAR(255) NOT NULL,
    l_state CHAR(2) NOT NULL,

    PRIMARY KEY (lid)
);

CREATE TABLE IF NOT EXISTS cm_job (
    jid   INT NOT NULL AUTO_INCREMENT,
    j_title VARCHAR(255) NOT NULL,
    j_profession INT NOT NULL,
    j_salary_min INT,
    j_salary_max INT,
    j_company INT NOT NULL,
    j_location INT NOT NULL,

    PRIMARY KEY (jid),
    FOREIGN KEY (j_profession) REFERENCES cm_profession (pid),
    FOREIGN KEY (j_company) REFERENCES cm_company (cid),
    FOREIGN KEY (j_location) REFERENCES cm_location (lid)
);

CREATE TABLE IF NOT EXISTS cm_living_cost (
    lcid INT NOT NULL AUTO_INCREMENT,
    lc_location INT,
    lc_average_cost FLOAT,
    lc_housing_cost FLOAT,
    lc_food_cost FLOAT,
    lc_medical_cost FLOAT,

    PRIMARY KEY (lcid),
    FOREIGN KEY (lc_location) REFERENCES cm_location (lid)
);

CREATE TABLE IF NOT EXISTS cm_index (
    iid INT NOT NULL AUTO_INCREMENT,
    i_location INT NOT NULL,
    i_safety FLOAT,
    i_health FLOAT,
    i_crime FLOAT,
    i_traffic FLOAT,
    i_pollution FLOAT,
    i_quality_of_life FLOAT,
    i_groceries FLOAT,
    i_rent FLOAT,

    PRIMARY KEY (iid),
    FOREIGN KEY (i_location) REFERENCES cm_location (lid)
);

