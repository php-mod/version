Versions and Constraints for PHP
================================

This library parse versions, 
E.x.:
<code>1.0.0</code>
<code>1.0.2-stable</code>
<code>1.0.20-alpha2</code>.
It can parse constraints (like Composer versions),
E.x.:
<code>>=1.0 >=1.0,<2.0 >=1.0,<1.1 | >=1.2</code>,
<code>1.0.*</code>,
<code>~1.2</code>.

The goal of that is to let you check if a version matches a constraint,
or to check if a constraint is a subset of another constraint.

All that is done to let us select which version is compatible with a user constraints.

It works with the same rules of Composer versioning.
