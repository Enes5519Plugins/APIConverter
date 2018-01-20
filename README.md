# APIConverter
Turanic API Converter plugin for Turanic  

[![HitCount](http://hits.dwyl.io/Enes5519/APIConverter.svg)](http://hits.dwyl.io/Enes5519/APIConverter)

**Commands:**
-
- **/apiconvert <plugin-directory-name>**
- **/apiconvert --all**

## TODOS AND FEATURES
- [x] onCommand and execute functions added string 
- [x] Clear in functions php > 7.0 texts. Example: ?string $test => $test 
- [x] Clear function end return typehints (onyl php > 7.0) Example: function test() : ?int => function test()
- [x] Clear : void
- [x] Change imports
- [x] onRun($tick) => onRun(int $tick)