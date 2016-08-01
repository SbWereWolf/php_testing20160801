/*
предложить варианты оптимизации.
Запросы для создания таблиц:
*/
  
SELECT
  * 
from 
   data
  ,link
  ,info 
where 
  link.info_id = info.id 
  and link.data_id = data.id;

CREATE TABLE `info` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `desc` text default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

CREATE TABLE `data` (
  `id` int(11) NOT NULL auto_increment,
  `date` date default NULL,
  `value` INT(11) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

CREATE TABLE `link` (
  `data_id` int(11) NOT NULL,
  `info_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

/*
оптимизация для каких целей ? 
размеры таблиц ? 
какие поля действительно нужны ?

если оптимизировать для сферического коня в вакууме,
то тогда надо добавить индексы в таблицу `link`,
а что бы добавление новых записей не требовало перестройки PRIMARY ключа,
то  в таблицу `link` надо добавить ещё отдельную колонку для PRIMARY ключа

по выборке данных : наверное таблица `link` в выдаче не нужна,
поэтому в селекте оставляем только `data` и `info`,
и айдишники пользователям наверное не к чему

каждое сочетание `data` и `info`,
наверное уникально,
дубли будем отсекать специальным индексом
*/

ALTER TABLE  `link` 
  ADD  `id` BIGINT NULL 
  DEFAULT NULL 
  AUTO_INCREMENT 
  PRIMARY KEY 
  COMMENT  'первичный ключ' 
  FIRST
;
ALTER TABLE  `testing`.`link` 
  ADD INDEX  `i_link_data_id` (  `data_id` )
;
ALTER TABLE  `testing`.`link` 
  ADD INDEX  `i_link_info_id` (  `info_id` )
;
ALTER TABLE  `testing`.`link` 
  ADD UNIQUE  `i_link_data_id_info_id_uniq` (  `data_id` ,  `info_id` )
;

SELECT
  data.`date` AS 'date',
  data.`value` AS 'value',
  info.`name` AS 'name',
  info.`desc` AS 'desc'
FROM data
  JOIN link
    ON link.data_id = data.id
  JOIN info
    ON link.info_id = info.id

/*

кодировка БД и всех таблиц должна быть UTF8,
использование cp1251 - чуть чуть вчерашний день
    
*/