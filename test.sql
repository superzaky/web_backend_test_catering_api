 SELECT facilities.*, locations.*, GROUP_CONCAT(tags.name) as tags
        FROM facilities
        JOIN locations ON facilities.location_id = locations.id
        LEFT JOIN facility_tags ON facilities.id = facility_tags.facility_id
        LEFT JOIN tags ON facility_tags.tag_id = tags.id
        WHERE facilities.id = 14;