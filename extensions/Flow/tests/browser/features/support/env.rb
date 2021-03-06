require 'mediawiki_selenium'

require 'mediawiki_selenium/support'
require 'mediawiki_selenium/step_definitions'

require_relative 'div_extension'

def env_or_default(key, default)
  ENV[key].nil? ? default : ENV[key].to_i
end

PageObject.default_page_wait = env_or_default 'PAGE_WAIT_TIMEOUT', 10
PageObject.default_element_wait = env_or_default 'ELEMENT_WAIT_TIMEOUT', 30
