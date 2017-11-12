import React from 'react'

const GridX = ({className = "", children}) => 
    <div className={"grid-x " + className}>
        {children}
    </div>

const Cell = ({className, children}) =>
    <div className={"cell " + className}>
        {children}
    </div>

export { GridX, Cell }