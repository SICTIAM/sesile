import React, { Component } from 'react'
import { func, object, string } from 'prop-types'
import Draggable from 'react-draggable'

export default class DraggablePosition extends Component {

    static propTypes = {
        className: string,
        position: object.isRequired,
        bounds: object,
        label: string.isRequired,
        style: object,
        handleChange: func.isRequired
    }
    
    static defaultProps = {
        position: {x:10 , y:10},
        bounds  : {top: 10, left: 10, right: 134, bottom: 253},
        style: {height: '300px', width: '190px', position: 'relative', overflow: 'auto', padding: '0'}
    }

    handleDrag = (e, ui) => {
        this.props.handleChange({x: ui.x, y: ui.y})
    }

    render() {
        const { className, label, style, bounds, labelColor, helpText } = this.props
        return (
            <div style={{display: 'inline-block'}} className={className}>
                <div className="draggable-position-box" style={style}>
                    <Draggable position={this.props.position} bounds={bounds} onDrag={this.handleDrag}>
                        <div className="draggable-position-box" style={this.props.boxStyle}>
                            {label}
                        </div>
                    </Draggable>
                </div>
                <p className="help-text">{helpText}</p> 
            </div>
        )
    }
}