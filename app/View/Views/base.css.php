<?php
    header("Content-type: text/css; charset: UTF-8");
?>

.grid-container {
    display: grid;
    width: 100%;
    height: 100vh;
    grid-template-areas:
        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."

        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . H   H H H   H H H   H . .   . . ."
        ". . .   . . H   H H H   H H H   H . .   . . ."

        ". . .   . . H   H H H   H H H   H . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."

        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."

        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."

        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . ."
        ". . .   . . .   . . .   . . .   . . .   . . .";
    grid-template-rows:    2fr 0.25fr 0.75fr   1fr 0.5fr 1.5fr   0.75fr 1.25fr 1fr   1fr 1.25fr 0.75fr   1.5fr 0.5fr 1fr   0.75fr 0.25fr 2fr;
    grid-template-columns: 2fr 0.25fr 0.75fr   1fr 0.5fr 1.5fr   0.75fr 1.25fr 1fr   1fr 1.25fr 0.75fr   1.5fr 0.5fr 1fr   0.75fr 0.25fr 2fr;
}

.grid-element-1 {
    grid-area: H;
}
