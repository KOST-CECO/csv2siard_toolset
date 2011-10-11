<?php
class A {
    var $T1 = "Text aus A\n";
    var $N1 = 11;
  
    function example() {
        echo "I am A::example() and provide basic functionality\n";
        echo "N1: $this->N1\n";
    }
    function e1() {
        $this->e2();
        echo "I am A e1\n";
    }
    
    function e2() {
        echo "I am A e2\n";
    }

}

class B extends A {
    function example() {
        echo "I am B::example() and provide additional functionality\n";
        echo $this->T1;
        echo "N1: $this->N1\n";
        $this->N1 = $this->N1 + 22;
        echo "N1: $this->N1\n";
        parent::example();
    }
}
$a = new A;
$a->e1();


$b = new B;
// This will call B::example(), which will in turn call A::example().
$b->example();
?> 
