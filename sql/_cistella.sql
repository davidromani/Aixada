/*
 * Member
 */
create table cistella_turn (
  id 	       		int			not null auto_increment,
  uf_id      		int    	    not null,
  date_turn         date    	not null,  
  ts			  	timestamp not null default current_timestamp,
  primary key (id),
  foreign key (uf_id)  references aixada_uf(id) on delete cascade
) engine=InnoDB default character set utf8 collate utf8_general_ci;