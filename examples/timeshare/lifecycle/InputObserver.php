<?php
namespace plibv4\process\examples\lifecycle;
interface InputObserver {
	function onInput(Input $input, string $c);
}
