#!/bin/bash

for FILENAME in `find . -type f`
do
cat "$FILENAME"
done