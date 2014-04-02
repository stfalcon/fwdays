#!/bin/bash

for FILENAME in `find . -type f`
do
echo "BEGIN $FILENAME"
cat "$FILENAME"
echo "END $FILENAME"
done