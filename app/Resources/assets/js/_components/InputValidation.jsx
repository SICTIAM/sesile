import React, { Component } from 'react'
import Validator from 'validatorjs'
import moment from 'moment'
import { Input } from './Form'

export default class InputValidation extends Component {
    state = { isValid: true, errorMessage: '' }
    static defaultProps = { value: '', type: '', accept: '', className: '' }
    validateValue = () => {
        const value = this.props.type === 'date' ? moment(this.props.value).format('MM.DD.YYYY') : this.props.value
        const validation = new Validator({ field: value }, { field: this.props.validationRule }, this.props.customErrorMessages)
        validation.setAttributeNames({ field: this.props.id })
        const isValid = validation.passes()
        const errorMessage = validation.errors.first('field') || ''
        this.setState({ isValid: isValid, errorMessage: errorMessage })
    }
    render() {
        return (
            <div className={this.props.className}>
                <Input  id={this.props.id}
                        labelText={this.props.labelText}
                        type={this.props.type}
                        className={this.props.className}
                        placeholder={this.props.placeholder}
                        value={this.props.value}
                        onChange={this.props.onChange}
                        onBlur={this.validateValue}
                        helpText={this.props.helpText}>
                    {(!this.state.isValid) && (
                        <span style={{color:"red"}}>
                            {this.state.errorMessage}
                        </span>)}
                </Input>

            </div>
        )
    }
}
