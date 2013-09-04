README FILE

Biblioteca para instalação de Componentes
Claudio Rubens Silva Filho
10/12/2012

CONTEÚDO
I.	INTRODUÇÃO
I.	COMO CRIAR UM COMPONENTE
I.	COMO INICIALIZAR UM COMPONENTE
II.	RECURSOS NECESSÁRIOS
III.	EXEMPLOS



I. INTRODUÇÃO
1. Um componente é um recurso adicional, muitas vezes desenvolvido por terceiros, que agrega novas funcionalidades ao software.

2. Um componente possui, por padrão, a estrutura de pastas a seguir:
	sys
	 |
	 --> lib
	      |
	      --> comps
	      	    |
	      	    --> pastaComNomeDoComponente
				|
				--> classes
				--> dic
				--> src
				--> install.xml

sys/lib: 
O caminho padrão do sistema para armazenar componentes.
A pasta raíz (sys) pode ter outro nome caso este tenha sido definido no arquivo global config.xml, na tag <config id='folderSys'>...</config>.

Esta pasta também contém o seguinte conteúdo, que NÃO DEVE ser apagado:

sys/lib
    |
    -->classes
	   |
	   --> IComponent.php
	   --> LibComponent.php
	   --> LoadInstallXml.php
	
    |
    -->dic
	   |
	   --> eLibComponent.xml
	   --> eLoadInstallXml


2. A utilização de componentes é feita por meio da classe sys/classes/util/Component, que
serve como fábrica para o componente solicitado na aplicação.
A estrutura 
Windows
1. Insert the CD-ROM into the CD-ROM drive.
2. Click on "My Computer".
3. Click on the CD icon.
4. Click on the START#.htm file (# being the disk number of the CD inserted).
5. View through browser interface for desired media.

Macintosh
1. Insert the CD-ROM into the CD-ROM drive.
2. Click on the CD icon that appears on your desktop.
3. Click on the START#.htm file (# being the disk number of the CD inserted).
4. View through browser interface for desired media.


II. MINIMUM SYSTEM REQUIREMENTS [Update for your product]


Windows
¥ Windows 98, 2000, XP
¥ Pentium 366 MHz or higher 
¥ Minimum 64 MB of RAM
¥ Screen resolution 800x600 or higher
¥ Minimum 64 MB RAM
¥ Quad-speed CD-ROM drive
¥ Internet Explorer 5.5, 6.0, Netscape 7.0


Macintosh
Mac 9.2, 10.2, 10.2
¥ G3 or higher Macintosh required
¥ Minimum 64 MB available RAM 
¥ Screen resolution 800x600 or higher
¥ Quad-speed CD-ROM drive 
¥ Internet Explorer 5.2, Netscape 7.0, Safari 1.2


III. SOFTWARE/PLUG-IN DOWNLOADS [Update for your product]
QuickTime 6.0 or later
http://www.apple.com/quicktime/download/
Macromedia Shockwave 8.0 and Flash 6.0 or later
http://sdc.shockwave.com/shockwave/download/

IV. KNOWN ISSUES AND WORK AROUNDS [Update for your product or 
delete if your product has none]


V. TECHNICAL SUPPORT 
If you need technical assistance, you may contact our technical support department in 
the following ways:
1. Call1-800-677-6337 Mon - Fri 8:00 am to 8:00 pm Eastern
2. Visit http://247.aw.com.  Email tech support is available 24/7.

Copyright © [copyright year] Pearson Education, Inc. publishing as [imprint name - 
Benjamin Cummings or Addison Wesley].

ISBN [Update for your product]

