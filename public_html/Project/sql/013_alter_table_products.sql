ALTER TABLE Products
ADD column avg_rating decimal(1,1) default 0.0;
ALTER TABLE Products
CHECK (stock >= 0);
ALTER TABLE Products
ADD column num_rating int AUTO_INCREMENT;

