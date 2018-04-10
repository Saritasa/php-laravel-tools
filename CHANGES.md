# Changes History
1.3.0
-----
Allow to generate DTOs for all models by one command

1.2.0
-----
+ Allow to avoid generating DTO constants with attribute names
+ Removed user interaction to configure immutable and strict typed DTO. Moved to command options and config.

1.1.0
-----
+ Add feature to create immutable and\or strict-typed DTO

1.0.0
-----
+ Add model-based DTO generation command
+ Improve PHPDoc class description builder to allow build description without properties
+ Extract parent ClassFactory and ModelBasedClassFactory to simplify adding new classes factory

0.1.10
-----
+ Add more tests

0.1.9
-----
+ Resolve issue with form request factory placeholders building

0.1.8
-----
+ Improve form request scaffold console command output

0.1.7
-----
+ Improve Readme with publishing config instructions

0.1.6
-----
+ Add laravel package discovery
+ Change visibility of form request factory methods
+ Improve readme with links

0.1.5
-----
+ Add more unit tests

0.1.4
-----
+ Resolve issue with class property description access type
+ Add PhpDoc renderer functions tests

0.1.3
-----
Resolve issue with string representation of form request rules attribute

0.1.2
-----
Improve generated class description format

0.1.1
-----
+ Improve PhpDoc block for form request class generation
+ Add importing of used classes in template functionality
+ Add basic tests 

0.1.0
-----
Initial version
