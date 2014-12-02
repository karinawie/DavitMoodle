<!--
 @package    mod_davit
 @copyright  2014 Karina Wiechork <karinawiechork@gmail.com>
 @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 -->

<script type="text/javascript" src="Davit/main.js"></script>
<script type="text/javascript" src="CodeMirror/js/codemirror.js"></script>
<script type="text/javascript" src="CodeMirror/js/mirrorframe.js"></script>


<?php if(isset($suss) && $suss){?>
<div style="padding: 10px;margin: 5px;background-color: #bce8f1;border-radius: 5px;">
    <span>Atividade salva com sucesso!</span>
</div>
<?php }?>

<?php if(isset($suss) && !$suss){?>
<div style="padding: 10px;margin: 5px;background-color: #FBC2C4;border-radius: 5px;">
    <span>Erro</span>
</div>
<?php } ?>

<?php if(isset($usuario)){?>
<table>
    <tr>
        <td><span><b>Nome do aluno: </b></span></td>
        <td><span><b><?php echo $usuario->firstname.' '.$usuario->lastname?></b></span></td>
    </tr>
</table>
<?php } ?>
<table>
    <!--tr><h3>Programe o Robô Davit!</h3-->
    <td>
        <canvas id="showCanvas" width="500" height="500" style="border:solid 1px red; padding:0px; background: #222; margin:0px; width: 400px;"></canvas>
    </td>
    <td>
        <form action="view.php?id=<?php echo $id; ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="n" value="<?php echo $n; ?>">
            <input type="hidden" name="userid" value="<?php echo $activity->userid; ?>">

            <strong>Selecionar Cenário:</strong></p>
            <select id="level" name="level" onchange="javascript:eval(this.value)">
                <?php echo davit_options_dificuldade($activity->level); ?>
            </select>

            <div style="margin-left:20px; background-color:#ffffff;">
                <textarea name="programa" id=programaDavit style='width:230px;height:290px;'></textarea>
            </div>
            <input type="hidden" name="actionAT" value="<?php echo $activity->actionAT; ?>" />
            
            <div id="viewmode" class="tip showmode"  style="margin-left:20px;">
                <input type="submit" name="save" value="Salvar" />
                <!--input type=button value='Restaurar' onclick='setupReset();'/--> 
                <input type=button value='Executar' onclick='this.value; exec(editor.getCode());'/>
            </div>
            
        </form>

    </td>
</tr>
</table>
<script type="text/javascript">
    //main();
    var sExemploCodigo = " /*Altere as linhas abaixo para fazer o Davit pegar os discos!*/ \n\nmove();\nmove();\nturn();\nmove();\ngetDisc(); <?php echo str_replace(array("\r\n", '\r\n'), '\n', $activity->text); ?>";
    var textarea = document.getElementById('programaDavit');
    var editor = CodeMirror.fromTextArea(textarea, {parserfile: ["tokenizejavascript.js", "parsejavascript.js"], path: "CodeMirror/js/", stylesheet: "CodeMirror/css/jscolors.css", content: sExemploCodigo});
    document.getElementById('level').dispatchEvent(new Event('change'));
</script>