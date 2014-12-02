function exec(psCode)
{
   setupExerc1();
   try{ eval(psCode); } catch(ex) { alert(ex); }
}
function setupExerc1()
{
makeworld(9,9,true); 
createLongWall([3,1], 'L',2);
createLongWall([1,1], 'O',2);
createWall([3,3], 'S');
createWall([1,3], 'S');
createWall([2,3], 'L');
createWall([2,3], 'O');
createLongWall([1,1],'S',3);

createDisc([8,0],"rgb(0,0,255)");
createDisc([8,1],"rgb(0,0,255)");
createDisc([8,2],"rgb(0,0,255)");
createDisc([8,3],"rgb(0,0,255)");
createDisc([8,4],"rgb(0,0,255)");
createDisc([8,5],"rgb(0,0,255)");
createDisc([8,6],"rgb(0,0,255)");
createDisc([8,7],"rgb(0,0,255)");
createDisc([8,8],"rgb(0,0,255)");

createDisc([7,0],"rgb(255,255,0)");
createDisc([7,1],"rgb(255,255,0)");
createDisc([7,2],"rgb(255,255,0)");
createDisc([7,3],"rgb(255,255,0)");
createDisc([7,4],"rgb(255,255,0)");
createDisc([7,5],"rgb(255,255,0)");
createDisc([7,6],"rgb(255,255,0)");
createDisc([7,7],"rgb(255,255,0)");
createDisc([7,8],"rgb(255,255,0)");
createDavit(4,4,'L');
}



var sExc3_1="var tem_sol=true;\n\nif(tem_sol)\n{\n   /* instruções que levam Davit até a praia */\n   move();\n   move();\n   move();\n}\nelse\n{\n   /* instruções que levam Davit para casa */\n   turn();\n   turn();\n   move();\n   move();\n   turn();\n   turn();\n   turn();\n   move();\n   move();\n   move();\n}";
var sExc3_2="var tem_sol=false;\nvar tem_vento=true;\nvar clima_bom=tem_sol||tem_vento;\nif(clima_bom)\n{\n   /* instruções que levam Davit até a praia */\n   move();\n   move();\n   move();\n}\nelse\n{\n   /* instruções que levam Davit para casa */\n   turn();\n   turn();\n   move();\n   move();\n   turn();\n   turn();\n   turn();\n   move();\n   move();\n   move();\n}";
var sExc3_3="var tem_sol=false;\nvar tem_vento=true;\nvar tem_gasolina=false;\nvar clima_bom=tem_sol||tem_vento;\nif(clima_bom&&tem_gasolina)\n{\n   /* instruções que levam Davit até a praia */\n   move();\n   move();\n   move();\n}\nelse\n{\n   /* instruções que levam Davit para casa */\n   turn();\n   turn();\n   move();\n   move();\n   turn();\n   turn();\n   turn();\n   move();\n   move();\n   move();\n}";
var sExc3_4="var tem_sol=true;\nvar tem_vento=true;\nvar tem_gasolina=true;\nvar pneu_furado=true;\nvar clima_bom=tem_sol || tem_vento;\nvar carro_ok=tem_gasolina && !pneu_furado;\nif(clima_bom && carro_ok)\n{\n   /* instruções que levam Davit até a praia */\n   move();\n   move();\n   move();\n}\nelse\n{\n   /* instruções que levam Davit para casa */\n   turn();\n   turn();\n   move();\n   move();\n   turn();\n   turn();\n   turn();\n   move();\n   move();\n   move();\n}";
var sExc3_5="alert(\"Nas próximas quatro perguntas criaremos o mundo de Davit.\")\nvar tem_sol=confirm(\"Posso criar um dia ensolarado no mundo de Davit?\");\nvar tem_vento=confirm(\"Enviamos agora um pouco de vento para Davit velejar?\");\nvar tem_gasolina=confirm(\"Colocamos gasolina no carro de Davit?\");\nvar pneu_furado=confirm(\"Última pergunta: Furamos o pneu de Davit?\");\nvar clima_bom=tem_sol || tem_vento;\nvar carro_ok=tem_gasolina && !pneu_furado;\nif(clima_bom && carro_ok)\n{\n   /* instruções que levam Davit até a praia */\n   move();\n   move();\n   move();\n}\nelse\n{\n   /* instruções que levam Davit para casa */\n   turn();\n   turn();\n   move();\n   move();\n   turn();\n   turn();\n   turn();\n   move();\n   move();\n   move();\n}";

var sCodigoAtual=sExc3_1;

setupExercMedio();
//var exc2_1   = document.getElementById('exc2_1');
/*var exc2_2   = document.getElementById('exc2_2');
var exc2_3   = document.getElementById('exc2_3');
var exc2_4   = document.getElementById('exc2_4');
var exc2_5   = document.getElementById('exc2_5');
var editor2_1 = CodeMirror.fromTextArea(exc2_1, {parserfile: ["tokenizejavascript.js", "parsejavascript.js"], path: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/js/", stylesheet: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/css/jscolors.css", content: sExc2_1});
var editor2_2 = CodeMirror.fromTextArea(exc2_2, {parserfile: ["tokenizejavascript.js", "parsejavascript.js"], path: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/js/", stylesheet: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/css/jscolors.css", content: sExc2_2});
var editor2_3 = CodeMirror.fromTextArea(exc2_3, {parserfile: ["tokenizejavascript.js", "parsejavascript.js"], path: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/js/", stylesheet: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/css/jscolors.css", content: sExc2_3});
var editor2_4 = CodeMirror.fromTextArea(exc2_4, {parserfile: ["tokenizejavascript.js", "parsejavascript.js"], path: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/js/", stylesheet: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/css/jscolors.css", content: sExc2_4});
var editor2_5 = CodeMirror.fromTextArea(exc2_5, {parserfile: ["tokenizejavascript.js", "parsejavascript.js"], path: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/js/", stylesheet: "http://www.aprenderprogramar.com.br/wp-content/uploads/CodeMirror/css/jscolors.css", content: sExc2_5});*/
