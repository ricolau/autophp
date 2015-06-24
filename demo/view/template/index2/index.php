
<br /><br />

<?php $this->slot('header');?>
<br /><br />

######## hi, i'm /index/index template ################
<br /><br />

<?php $this->slot('index/section');?>
<br /><br />


######## show i18n use here ################

current language is: <?php echo i18n::language();?>
<br /><br />

the author's gender is:<?php echo i18n::get('gender');?>
<br /><br />

and the vget of i18n is: <?php echo i18n::vget('telltime', array($title,$time));?>
<br /><br />



######## show render data below ################

hi, the title is: <?php echo $title;?>
<br /><br />

current time is: <?php echo $time;?>
<br /><br />

my name is:  <?php echo $name;?>
<br /><br />

info is: <?php var_dump($uinfo);?>
<br /><br />

you can get all assign data by: <font color="red"> var_dump($this->data); </font> or see it with the <font color="red">debugMode!</font>
<br /><br />




 <font color="red">
all assign data is: <?php var_dump($this->data);?>
<br /><br />
</font>

<?php $this->slot('footer');?>
<br /><br />


