import React, { Component } from 'react'
import PropTypes, { func, string } from 'prop-types'
import RichTextEditor from 'react-rte'

export default class Editor extends Component {
    static propTypes = {
        id: string.isRequired,
        handleChange: func.isRequired,
        className: string,
        label: string.isRequired,
        value: string
    }
    static defaultProps = {
        className: ''
    }

    state = {
        value: RichTextEditor.createEmptyValue(),
        isSet: false,
        suggestion: false,
        mouseX: '',
        mouseY: '',
        divX: '',
        divY: '',
        template: [],
        insertHere: '',
        autocomplete: ''
    }

    componentWillReceiveProps(nextProps) {
        if (!this.state.isSet && nextProps.value != null) {
            const value = RichTextEditor.createValueFromString(nextProps.value, 'html')
            this.setState({value: value, isSet: true, valueStr: nextProps.value})
        }
        if (nextProps.template != null) {
            this.setState({template: nextProps.template})
        }
    }

    handleInsert = (value) => {
        let openingBracket = 0
        let closingBracket = 0
        let nextOpening = 0
        let suggest = false

        for (let nbr = 0; nextOpening !== -1; nbr++) {
            openingBracket = value.indexOf("{{", nbr)
            nextOpening = value.indexOf("{{", (openingBracket + 1))
            closingBracket = value.indexOf("}}", nbr)
            if (nextOpening < closingBracket && nextOpening !== -1) {
                suggest = true
                let complete = value.substring(openingBracket + 2, openingBracket + 3)
                if (!complete.match(/[a-z]/i))
                    complete = ''
                this.setState({suggestion: true, insertHere: openingBracket + 2, autocomplete: complete})
            }
        }
        if (suggest === false) this.setState({suggestion: suggest})
    }

    handleChange = (value) => {
        this.setState({value})
        this.props.handleChange(this.props.id, value.toString('html'))
        this.handleInsert(value.toString('html'))
    }
    mouseSave = (e) => {
        this.setState({mouseX: e.nativeEvent.clientX, mouseY: e.nativeEvent.clientY})
    }
    mouse = (e) => {
        this.setState({divX: this.state.mouseX, divY: this.state.mouseY})
    }
    handleClick = (value) => {
        let valueInserted = this.state.value.toString('html')
        valueInserted = valueInserted.substring(0, this.state.insertHere) + value + "}}" + valueInserted.substring(this.state.autocomplete ? this.state.insertHere + 1 : this.state.insertHere)
        this.setState({value: RichTextEditor.createValueFromString(valueInserted, 'html'), suggestion:false})
    }

    render() {
        const listSuggest = this.state.template.map(lists => lists.includes(this.state.autocomplete) && <li key={lists}
                                                                                                            onClick={(e) => this.handleClick(e.target.textContent)}>{lists}</li>)
        return (
            <div className={this.props.className} onFocus={(e) => this.mouse(e)} onMouseMove={(e) => this.mouseSave(e)}>
                <label className="text-bold">
                    {this.props.label}
                </label>
                {this.state.suggestion &&
                <ul style={{
                    listStyleType: "none",
                    position: "absolute",
                    textAlign: "center",
                    border: "2px solid",
                    color: "black",
                    padding: "5px",
                    fontSize: "1em",
                    zIndex: "100",
                    background: "white",
                    top: `${this.state.divY}px`,
                    left: `${this.state.divX}px`
                }}>
                    {this.props.template.length > 0 &&
                    listSuggest
                    }
                </ul>}
                <RichTextEditor value={this.state.value} onChange={this.handleChange}/>
            </div>
        )
    }
}